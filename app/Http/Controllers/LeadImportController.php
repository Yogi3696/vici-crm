<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\VicidialList;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class LeadImportController extends Controller
{
    /**
     * Rows held in memory per INSERT. Large enough that a 50k-row file is a few
     * hundred round trips, small enough to stay well under max_allowed_packet.
     */
    private const CHUNK = 500;

    /**
     * Columns offered as mapping targets. vicidial_list carries a long tail of
     * site-specific columns, so the picker lists the standard Vicidial fields
     * first and only keeps the ones this database actually has.
     */
    private const MAPPABLE = [
        'phone_number' => 'Phone Number',
        'first_name' => 'First Name',
        'middle_initial' => 'Middle Initial',
        'last_name' => 'Last Name',
        'email' => 'Email',
        'address1' => 'Address 1',
        'address2' => 'Address 2',
        'address3' => 'Address 3',
        'city' => 'City',
        'state' => 'State',
        'province' => 'Province',
        'postal_code' => 'Postal Code',
        'country_code' => 'Country Code',
        'phone_code' => 'Phone Code',
        'alt_phone' => 'Alt Phone',
        'title' => 'Title',
        'gender' => 'Gender',
        'date_of_birth' => 'Date of Birth',
        'vendor_lead_code' => 'Vendor Lead Code',
        'source_id' => 'Source ID',
        'security_phrase' => 'Security Phrase',
        'comments' => 'Comments',
        'rank' => 'Rank',
        'owner' => 'Owner',
    ];

    public function create()
    {
        return view('leads.import.create', [
            'lists' => $this->lists(),
        ]);
    }

    /**
     * Step 1: take the upload, park it in storage and show the column mapper
     * pre-filled with header-name guesses. The file is not parsed beyond its
     * first rows here, so a large import costs nothing until it is confirmed.
     */
    public function preview(Request $request)
    {
        $data = $request->validate([
            'file' => ['required', 'file', 'mimes:csv,txt', 'max:20480'],
            'list_id' => ['required', 'integer', 'exists:vicidial_lists,list_id'],
            'delimiter' => ['required', 'in:comma,semicolon,tab,pipe'],
            'has_header' => ['nullable', 'boolean'],
            'status' => ['nullable', 'string', 'max:6'],
            'duplicate_check' => ['nullable', 'in:none,list,all'],
        ]);

        $this->pruneAbandoned();

        $path = $request->file('file')->store('lead-imports');
        $delimiter = $this->delimiter($data['delimiter']);
        $hasHeader = (bool) ($data['has_header'] ?? false);

        [$rows, $total] = $this->sample(storage_path('app/'.$path), $delimiter);

        if ($rows === []) {
            return back()
                ->withInput()
                ->withErrors(['file' => __('The file appears to be empty.')]);
        }

        $header = $hasHeader ? $rows[0] : [];
        $sample = $hasHeader ? array_slice($rows, 1, 5) : array_slice($rows, 0, 5);

        session()->put('lead_import', [
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
            'list_id' => (int) $data['list_id'],
            'delimiter' => $data['delimiter'],
            'has_header' => $hasHeader,
            'status' => $data['status'] ?: 'NEW',
            'duplicate_check' => $data['duplicate_check'] ?? 'none',
            'columns' => count($rows[0]),
        ]);

        return view('leads.import.map', [
            'header' => $header,
            'sample' => $sample,
            'columns' => count($rows[0]),
            'rowCount' => $hasHeader ? max($total - 1, 0) : $total,
            'fileName' => $request->file('file')->getClientOriginalName(),
            'list' => VicidialList::find($data['list_id']),
            'fields' => $this->fields(),
            'guesses' => $this->guessMapping($header),
        ]);
    }

    /**
     * Step 2: stream the file through the confirmed mapping and insert in
     * chunks. Rows without a usable phone number are skipped rather than
     * failing the run, since a single bad line should not cost the whole file.
     */
    public function store(Request $request)
    {
        $meta = session('lead_import');

        if (! $meta || ! file_exists(storage_path('app/'.$meta['path']))) {
            return redirect()
                ->route('leads.import.create')
                ->withErrors(['file' => __('The upload expired. Please choose the file again.')]);
        }

        $request->validate([
            'mapping' => ['required', 'array'],
            'mapping.*' => ['nullable', 'string'],
        ]);

        $mapping = $this->cleanMapping($request->input('mapping', []));

        if (! in_array('phone_number', $mapping, true)) {
            return back()
                ->withInput()
                ->withErrors(['mapping' => __('One column must be mapped to Phone Number.')]);
        }

        $result = $this->import(
            storage_path('app/'.$meta['path']),
            $this->delimiter($meta['delimiter']),
            $meta['has_header'],
            $mapping,
            $meta['list_id'],
            $meta['status'],
            $meta['duplicate_check'],
        );

        Storage::delete($meta['path']);
        session()->forget('lead_import');

        $message = __(':imported leads imported into :list.', [
            'imported' => number_format($result['imported']),
            'list' => optional(VicidialList::find($meta['list_id']))->list_name ?: $meta['list_id'],
        ]);

        if ($result['skipped']) {
            $message .= ' '.__(':skipped rows skipped (no phone number or duplicate).', [
                'skipped' => number_format($result['skipped']),
            ]);
        }

        return redirect()
            ->route('leads.index', ['list_id' => $meta['list_id']])
            ->with('status', $message);
    }

    /**
     * Reads the file once, keeping only the first rows for the preview while
     * counting the rest, so the mapper can show a row total without loading a
     * large file into memory.
     */
    private function sample(string $path, string $delimiter): array
    {
        $rows = [];
        $total = 0;
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return [[], 0];
        }

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null]) {
                continue; // blank line
            }

            $total++;

            if (count($rows) < 6) {
                $rows[] = array_map(fn ($v) => (string) $v, $row);
            }
        }

        fclose($handle);

        return [$rows, $total];
    }

    private function import(
        string $path,
        string $delimiter,
        bool $hasHeader,
        array $mapping,
        int $listId,
        string $status,
        string $duplicateCheck
    ): array {
        $handle = fopen($path, 'r');

        if ($handle === false) {
            return ['imported' => 0, 'skipped' => 0];
        }

        $imported = 0;
        $skipped = 0;
        $batch = [];
        $first = true;
        $now = Carbon::now();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null]) {
                continue;
            }

            if ($first) {
                $first = false;

                if ($hasHeader) {
                    continue;
                }
            }

            $lead = $this->buildRow($row, $mapping, $listId, $status, $now);

            if ($lead === null) {
                $skipped++;
                continue;
            }

            if ($duplicateCheck !== 'none' && $this->isDuplicate($lead['phone_number'], $listId, $duplicateCheck)) {
                $skipped++;
                continue;
            }

            $batch[] = $lead;

            if (count($batch) >= self::CHUNK) {
                Lead::insert($batch);
                $imported += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            Lead::insert($batch);
            $imported += count($batch);
        }

        fclose($handle);

        return ['imported' => $imported, 'skipped' => $skipped];
    }

    /**
     * Turns one CSV row into an insertable attribute array, or null when it
     * carries no digits in the phone column.
     *
     * Every mapped column is present on every row, blank cells included: a
     * batch insert builds one statement from the first row's keys, so a row
     * that omitted its empty columns would shift the values out of alignment.
     */
    private function buildRow(array $row, array $mapping, int $listId, string $status, Carbon $now): ?array
    {
        $lead = [];

        foreach ($mapping as $index => $column) {
            $value = trim((string) ($row[$index] ?? ''));

            $lead[$column] = $column === 'phone_number'
                ? preg_replace('/\D/', '', $value)
                : $value;
        }

        if (empty($lead['phone_number'])) {
            return null;
        }

        return array_merge($lead, [
            'list_id' => $listId,
            'entry_list_id' => $listId,
            'status' => $status,
            'entry_date' => $now,
            'modify_date' => $now,
            'called_count' => 0,
            'called_since_last_reset' => 'N',
        ]);
    }

    private function isDuplicate(string $phone, int $listId, string $scope): bool
    {
        $query = Lead::where('phone_number', $phone);

        if ($scope === 'list') {
            $query->where('list_id', $listId);
        }

        return $query->exists();
    }

    /**
     * Drops unmapped columns and anything not on the whitelist, keeping only
     * the first column mapped to a given field so a duplicate pick cannot
     * overwrite an earlier one.
     */
    private function cleanMapping(array $mapping): array
    {
        $allowed = array_keys($this->fields());
        $clean = [];

        foreach ($mapping as $index => $column) {
            if (! $column || ! in_array($column, $allowed, true) || in_array($column, $clean, true)) {
                continue;
            }

            $clean[(int) $index] = $column;
        }

        return $clean;
    }

    /**
     * Header-name matching for the mapper's initial state: an exact column-name
     * hit first, then a loose match on the label.
     */
    private function guessMapping(array $header): array
    {
        if ($header === []) {
            return [];
        }

        $fields = $this->fields();
        $guesses = [];
        $taken = [];

        foreach ($header as $index => $name) {
            $normal = $this->normalize($name);

            if ($normal === '') {
                continue;
            }

            foreach ($fields as $column => $label) {
                if (in_array($column, $taken, true)) {
                    continue;
                }

                if ($normal === $column || $normal === $this->normalize($label)) {
                    $guesses[$index] = $column;
                    $taken[] = $column;
                    break;
                }
            }
        }

        return $guesses;
    }

    /**
     * Clears staged uploads left behind when someone reaches the mapping step
     * and never confirms. A day is well past any session lifetime, so nothing
     * still reachable is removed.
     */
    private function pruneAbandoned(): void
    {
        $cutoff = Carbon::now()->subDay()->getTimestamp();

        foreach (Storage::files('lead-imports') as $file) {
            if (Storage::lastModified($file) < $cutoff) {
                Storage::delete($file);
            }
        }
    }

    /** Lowercases and reduces a header or label to a comparable key. */
    private function normalize(string $value): string
    {
        return trim(preg_replace('/[^a-z0-9]+/', '_', strtolower($value)), '_');
    }

    /** Mappable fields that exist on this installation's vicidial_list table. */
    private function fields(): array
    {
        $existing = Schema::getColumnListing('vicidial_list');

        return Arr::only(self::MAPPABLE, array_intersect(array_keys(self::MAPPABLE), $existing));
    }

    private function delimiter(string $name): string
    {
        return [
            'comma' => ',',
            'semicolon' => ';',
            'tab' => "\t",
            'pipe' => '|',
        ][$name] ?? ',';
    }

    private function lists()
    {
        return VicidialList::orderBy('list_name')->get(['list_id', 'list_name']);
    }
}

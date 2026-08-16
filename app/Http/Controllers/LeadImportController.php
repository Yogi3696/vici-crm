<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\VicidialList;
use App\Models\VicidialStatus;
use Illuminate\Validation\Rule;
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
            'statuses' => $this->statuses(),
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
            'status' => ['required', 'string', 'max:6', Rule::in($this->statuses()->pluck('status'))],
            'duplicate_check' => ['nullable', 'in:none,list,all'],
            'reset_dialable' => ['nullable', 'boolean'],
            'skip_invalid_phone' => ['nullable', 'boolean'],
            'phone_code' => ['nullable', 'string', 'max:10'],
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
            'status' => $data['status'],
            'duplicate_check' => $data['duplicate_check'] ?? 'none',
            'reset_dialable' => (bool) ($data['reset_dialable'] ?? false),
            'skip_invalid_phone' => (bool) ($data['skip_invalid_phone'] ?? false),
            'phone_code' => trim((string) ($data['phone_code'] ?? '')),
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
            'status' => $data['status'],
            'dialable' => (bool) ($data['reset_dialable'] ?? false),
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

        $result = $this->import(storage_path('app/'.$meta['path']), $mapping, $meta);

        Storage::delete($meta['path']);
        session()->forget('lead_import');

        $message = __(':imported leads imported into :list with status :status.', [
            'imported' => number_format($result['imported']),
            'list' => optional(VicidialList::find($meta['list_id']))->list_name ?: $meta['list_id'],
            'status' => $meta['status'],
        ]);

        foreach (['no_phone' => __(':n rows had no phone number.'),
                  'invalid_phone' => __(':n rows had an invalid phone number.'),
                  'duplicate' => __(':n rows were duplicates.')] as $key => $line) {
            if ($result[$key]) {
                $message .= ' '.str_replace(':n', number_format($result[$key]), $line);
            }
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

    private function import(string $path, array $mapping, array $meta): array
    {
        $handle = fopen($path, 'r');

        $result = ['imported' => 0, 'no_phone' => 0, 'invalid_phone' => 0, 'duplicate' => 0];

        if ($handle === false) {
            return $result;
        }

        $delimiter = $this->delimiter($meta['delimiter']);
        $batch = [];
        $first = true;
        $now = Carbon::now();

        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if ($row === [null]) {
                continue;
            }

            if ($first) {
                $first = false;

                if ($meta['has_header']) {
                    continue;
                }
            }

            $lead = $this->buildRow($row, $mapping, $meta, $now);

            if (! is_array($lead)) {
                $result[$lead]++; // 'no_phone' or 'invalid_phone'
                continue;
            }

            if ($meta['duplicate_check'] !== 'none'
                && $this->isDuplicate($lead['phone_number'], $meta['list_id'], $meta['duplicate_check'])) {
                $result['duplicate']++;
                continue;
            }

            $batch[] = $lead;

            if (count($batch) >= self::CHUNK) {
                Lead::insert($batch);
                $result['imported'] += count($batch);
                $batch = [];
            }
        }

        if ($batch !== []) {
            Lead::insert($batch);
            $result['imported'] += count($batch);
        }

        fclose($handle);

        return $result;
    }

    /**
     * Turns one CSV row into an insertable attribute array, or returns a string
     * reason ('no_phone' / 'invalid_phone') when the row cannot be dialled.
     *
     * Every mapped column is present on every row, blank cells included: a
     * batch insert builds one statement from the first row's keys, so a row
     * that omitted its empty columns would shift the values out of alignment.
     *
     * @return array|string
     */
    private function buildRow(array $row, array $mapping, array $meta, Carbon $now)
    {
        $lead = [];

        foreach ($mapping as $index => $column) {
            $value = trim((string) ($row[$index] ?? ''));

            $lead[$column] = $column === 'phone_number'
                ? preg_replace('/\D/', '', $value)
                : $value;
        }

        if (empty($lead['phone_number'])) {
            return 'no_phone';
        }

        // Vicidial will not dial a number it cannot place: the column holds 18
        // digits, and anything under 7 is a fragment rather than a number.
        if ($meta['skip_invalid_phone']
            && (strlen($lead['phone_number']) < 7 || strlen($lead['phone_number']) > 18)) {
            return 'invalid_phone';
        }

        $lead = array_merge($lead, [
            'list_id' => $meta['list_id'],
            'entry_list_id' => $meta['list_id'],
            'status' => $meta['status'],
            'entry_date' => $now,
            'modify_date' => $now,
            'called_count' => 0,
            // Leaving this 'N' is what makes a lead dialable on the next pass;
            // 'Y' would park it until the list is reset.
            'called_since_last_reset' => $meta['reset_dialable'] ? 'N' : 'Y',
        ]);

        // A file-supplied phone_code wins; the fallback only fills the gap.
        if ($meta['phone_code'] !== '' && empty($lead['phone_code'])) {
            $lead['phone_code'] = $meta['phone_code'];
        }

        return $lead;
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

    /**
     * Statuses offered for freshly imported leads. NEW leads a list, so it is
     * pinned to the top; the rest follow alphabetically for the cases where a
     * file is loaded already-dispositioned.
     */
    private function statuses()
    {
        return VicidialStatus::orderBy('status')
            ->get(['status', 'status_name'])
            ->sortBy(fn ($s) => $s->status === 'NEW' ? '' : $s->status)
            ->values();
    }
}

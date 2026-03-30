@extends('layouts.app')

@section('header')
<div class="page-hero">
    <div>
        <h1 class="page-title">Forwarded Documents</h1>
        <p class="page-subtitle">Transfer history and destination units for forwarded files</p>
    </div>
</div>
@endsection

@section('content')

<div class="pb-2">
    <form method="GET" action="{{ route('forwarded.index') }}" class="filter-card px-4 py-4 md:px-5 border border-[#d8e2f0] bg-gradient-to-b from-white to-[#f8fbff]" data-live-search-form>
        <div class="flex flex-col gap-3 md:flex-row md:items-end">
            <div class="min-w-0 md:w-1/3">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Filter by unit</label>
                @php
                    $selectedUnitLabel = 'All units';
                    $pauSubUnits = ['Resumption NCO', 'TOP NCO', 'Restoration NCO', 'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO'];
                    $bgcuSubUnits = ['Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'];
                    foreach ($filterUnits as $unit) {
                        if ((string) $unit->id === (string) $selectedUnitId) {
                            $selectedUnitLabel = $unit->name;
                            break;
                        }
                    }
                @endphp
                <div class="unit-filter" data-filter-unit-picker>
                    <input type="hidden" name="unit_id" id="filter-unit-hidden-input" value="{{ $selectedUnitId ?? '' }}" data-filter-unit-input>
                    <button
                        type="button"
                        class="unit-filter-trigger"
                        data-filter-unit-trigger
                        aria-haspopup="listbox"
                        aria-expanded="false"
                        style="color: {{ $selectedUnitId ? '#111827' : '#6b7280' }};"
                    >
                        <span class="unit-filter-label" id="filter-unit-picker-label" data-filter-unit-label>{{ $selectedUnitLabel }}</span>
                        <svg class="unit-filter-chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M5 7l5 5 5-5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                    <div class="unit-filter-menu is-hidden" data-filter-unit-menu role="listbox">
                        <div
                            class="unit-filter-option"
                            data-filter-unit-option
                            data-unit-id=""
                            data-unit-name="All units"
                        >
                            All units
                        </div>
                        @foreach($filterUnits as $unit)
                            @if(in_array($unit->name, array_merge($pauSubUnits, $bgcuSubUnits), true))
                                @continue
                            @endif
                            @if($unit->name === 'PAU')
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="PAU"
                                    data-has-flyout="pau"
                                >
                                    <span>PAU</span>
                                    <svg class="unit-filter-option-icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M8 5l5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @elseif($unit->name === 'BGCU')
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="BGCU"
                                    data-has-flyout="bgcu"
                                >
                                    <span>BGCU</span>
                                    <svg class="unit-filter-option-icon" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                                        <path d="M8 5l5 5-5 5" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="unit-filter-option"
                                    data-filter-unit-option
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="{{ $unit->name }}"
                                >
                                    {{ $unit->name }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="min-w-0 md:flex-1">
                <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.06em] text-slate-500">Search document</label>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Document number, title, type, or unit"
                    class="w-full rounded-xl border border-slate-300 bg-white px-4 py-2.5 text-sm text-slate-700 shadow-sm outline-none transition duration-200 hover:border-slate-400 focus:border-blue-500 focus:ring-2 focus:ring-blue-200"
                >
            </div>
        </div>
    </form>
</div>

<!-- Filter flyouts -->
<div
    data-filter-flyout="pau"
    style="
        display:none;
        position:fixed;
        width:230px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.625rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    "
>
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        PAU SUB-UNITS
    </div>
    @foreach($filterUnits as $subUnit)
        @if(in_array($subUnit->name, $pauSubUnits, true))
            <div
                class="unit-filter-flyout-item"
                data-filter-flyout-item
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<div
    data-filter-flyout="bgcu"
    style="
        display:none;
        position:fixed;
        width:210px;
        background:white;
        border:1px solid #c7dcff;
        border-radius:0.625rem;
        box-shadow:0 8px 24px rgba(0,0,0,0.15);
        z-index:999999;
        overflow:hidden;
    "
>
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        BGCU SUB-UNITS
    </div>
    @foreach($filterUnits as $subUnit)
        @if(in_array($subUnit->name, $bgcuSubUnits, true))
            <div
                class="unit-filter-flyout-item"
                data-filter-flyout-item
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<!-- CENTER WRAPPER -->
<div class="py-6">
    <div class="table-shell">
        <div class="overflow-x-auto modern-scrollbar">
            <table class="table-modern">

                <!-- Table Head -->
                <thead class="table-head border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-center">#</th>
                        <th class="px-6 py-4 text-left">Document No.</th>
                        <th class="px-6 py-4 text-center">Document Title</th>
                        <th class="px-6 py-4 text-center">Sender Unit</th>
                        <th class="px-6 py-4 text-center">Type</th>
                        <th class="px-6 py-4 text-center">Forwarded To</th>
                        <th class="px-6 py-4 text-center">Status</th>
                        <th class="px-6 py-4 text-center">Forwarded At</th>
                        <th class="px-6 py-4 text-center">Actions</th>
                    </tr>
                </thead>

                <!-- Table Body -->
                <tbody>
                    @forelse ($forwardHistories as $history)
                        <tr class="table-row">
                            <td class="px-6 py-4 font-medium text-slate-700 text-center">
                                {{ $forwardHistories->firstItem() + $loop->index }}
                            </td>
                            <td class="px-6 py-4 font-semibold text-slate-900 text-left">
                                {{ $history->document->document_number }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $history->document->title }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $history->document->senderUnit->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="badge-chip bg-blue-50 text-blue-700">
                                    {{ $history->document->document_type }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                {{ $history->toUnit->name ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="badge-chip bg-violet-50 text-violet-700">
                                    Forwarded
                                </span>
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-center">
                                {{ $history->created_at->format('M d, Y h:i A') }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <a href="{{ route('documents.view', ['id' => $history->document->id]) }}"
                                   class="action-btn bg-slate-100 text-slate-700 hover:bg-slate-200">
                                    Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="table-empty">
                                <div class="flex flex-col items-center gap-2">
                                    <span class="text-lg">📄</span>
                                    <span>No forwarded documents found</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @include('partials.pagination-controls', ['paginator' => $forwardHistories])
    </div>
</div>

<!-- FLOATING UPLOAD BUTTON + MODAL -->
<div x-data="{ 
    open: false, 
    documentNumber: '',
    showSuccessUpload: false,
    async openModal() {
        this.open = true;
        await this.fetchDocumentNumber();
    },
    async fetchDocumentNumber() {
        try {
            const response = await fetch('{{ route('documents.next-number') }}');
            const data = await response.json();
            this.documentNumber = data.document_number;
        } catch (error) {
            console.error('Error fetching document number:', error);
        }
    },
    submitUpload(event) {
        event.preventDefault();
        this.open = false;
        this.showSuccessUpload = true;
        setTimeout(() => {
            event.target.submit();
        }, 1500);
    }
}">

    <!-- FLOATING BUTTON -->
    <button
        @click="openModal()"
        type="button"
        style="
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 9999;
            background-color: #0B1F3A;
            color: #ffffff;
            padding: 14px 22px;
            border-radius: 9999px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.35);
            font-weight: 600;
        "
    >
        ＋ Document
    </button>

    <!-- MODAL BACKDROP -->
    <div
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="open = false"
        class="fixed inset-0 z-[10000] flex items-center justify-center p-4"
        style="background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(4px);"
    >

        <!-- MODAL CARD -->
        <div
            @click.stop
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 scale-90"
            x-transition:enter-end="opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-90"
            class="bg-white shadow-2xl overflow-y-auto"
            style="width: 500px; max-height: 90vh; border-radius: 2rem;"
        >

            <!-- HEADER -->
            <div class="flex items-center justify-between px-2 py-2 border-b bg-gradient-to-r from-gray-50 to-white">
                <h2 class="text-l font-semibold text-gray-800 mb-0.5 px-4">
                    Upload New Document
                </h2>
                <button
                    @click="open = false"
                    type="button"
                    class="w-9 h-9 flex items-center justify-center rounded-full 
                           hover:bg-gray-200 text-gray-500 hover:text-gray-700 transition duration-200 mb-0.5 px-4"
                >
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- FORM -->
            <form
                action="{{ route('documents.store') }}"
                method="POST"
                enctype="multipart/form-data"
                class="px-6 py-4 space-y-2.5"
                @submit="submitUpload($event)"
            >
            @csrf

            <!-- Document Number -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Number <span class="text-red-500">*</span>
                </label>
                <input
                    name="document_number"
                    x-model="documentNumber"
                    required
                    readonly
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           bg-gray-50 text-gray-600 cursor-not-allowed
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200"
                    placeholder="Loading..."
                >
                <p class="text-xs text-gray-500 mt-1">Auto-generated document number</p>
            </div>

            <!-- Document Title -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Title <span class="text-red-500">*</span>
                </label>
                <input
                    name="title"
                    required
                    placeholder="Enter descriptive title"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                           focus:ring-2 focus:ring-blue-500 focus:border-blue-500 
                           outline-none text-sm transition duration-200 hover:border-gray-400"
                >
            </div>

            <!-- Receiving Unit -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Receiving Unit <span class="text-red-500">*</span>
                </label>

                <div id="unit-picker" style="position: relative;">
                    <button
                        type="button"
                        id="unit-picker-btn"
                        onclick="toggleUnitDropdown(event)"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                               bg-white outline-none text-sm transition duration-200
                               hover:border-gray-400 text-left flex items-center justify-between"
                        style="color: #6b7280;"
                    >
                        <span id="unit-picker-label">Select Receiving Unit</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <input type="hidden" name="receiving_unit_id" id="unit-hidden-input">

                    <div
                        id="unit-dropdown"
                        style="
                            display: none;
                            position: absolute;
                            top: calc(100% + 4px);
                            left: 0;
                            width: 100%;
                            background: white;
                            border: 1px solid #d1d5db;
                            border-radius: 0.625rem;
                            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
                            z-index: 99999;
                            overflow: hidden;
                            max-height: 220px;
                            overflow-y: auto;
                        "
                    >
                        @foreach($units as $unit)
                            @if($unit->id == auth()->user()->unit_id)
                                @continue
                            @endif
                            @if(in_array($unit->name, [
                                'Resumption NCO', 'TOP NCO', 'Restoration NCO',
                                'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO',
                                'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
                            ]))
                                @continue
                            @endif

                            @if($unit->name === 'PAU')
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="PAU"
                                    data-has-flyout="pau"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                >
                                    <span>PAU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            @elseif($unit->name === 'BGCU')
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="BGCU"
                                    data-has-flyout="bgcu"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:space-between; transition:background 0.15s;"
                                >
                                    <span>BGCU</span>
                                    <svg style="width:13px;height:13px;color:#9ca3af;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                    </svg>
                                </div>
                            @else
                                <div
                                    class="unit-row"
                                    data-unit-id="{{ $unit->id }}"
                                    data-unit-name="{{ $unit->name }}"
                                    style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
                                >
                                    {{ $unit->name }}
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                <p class="text-xs text-gray-500 mt-1">You cannot send to your own unit</p>
            </div>

            <!-- Document Type -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Document Type <span class="text-red-500">*</span>
                </label>

                <div id="doctype-picker" style="position: relative;">
                    <button
                        type="button"
                        id="doctype-picker-btn"
                        onclick="toggleDoctypeDropdown(event)"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                               bg-white outline-none text-sm transition duration-200
                               hover:border-gray-400 text-left flex items-center justify-between"
                        style="color: #6b7280;"
                    >
                        <span id="doctype-picker-label">Select document type</span>
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>

                    <input type="hidden" name="document_type" id="doctype-hidden-input">
                </div>

                {{-- "Others" free-text field — shown only when Others is selected --}}
                <div id="doctype-others-wrapper" style="display:none; margin-top:0.5rem;">
                    <input
                        type="text"
                        name="document_type_other"
                        id="doctype-others-input"
                        placeholder="Please specify document type"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               outline-none text-sm transition duration-200 hover:border-gray-400"
                    >
                    <p class="text-xs text-gray-500 mt-1">Please describe the document type</p>
                </div>
            </div>

            <!-- File Upload -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-1.5">
                    Attach File <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="file"
                        name="file"
                        required
                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                        class="block w-full text-sm text-gray-600
                               file:mr-4 file:rounded-lg file:border-0
                               file:bg-blue-50 file:text-blue-700
                               file:px-4 file:py-2 file:text-sm file:font-semibold
                               hover:file:bg-blue-100 transition duration-200
                               border border-gray-300 rounded-lg
                               focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                               cursor-pointer"
                    >
                </div>
                <p class="text-xs text-gray-500 mt-1">Accepted: PDF, DOC, DOCX, JPG, PNG (Max: 25MB)</p>
            </div>

            <!-- FOOTER -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button
                    type="button"
                    @click="open = false"
                    class="px-6 py-2.5 rounded-lg border-2 border-gray-300
                           text-gray-700 hover:bg-gray-50 transition duration-200 
                           font-semibold text-sm"
                >
                    Cancel
                </button>
                <button
                    type="submit"
                    class="px-6 py-2.5 rounded-lg text-white font-semibold text-sm
                           shadow-lg hover:shadow-xl transition duration-200
                           transform hover:-translate-y-0.5"
                    style="background-color:#0B1F3A;"
                >
                    Upload
                </button>
            </div>
            </form>

        </div>
    </div>

    <!-- SUCCESS UPLOAD MODAL -->
    <div x-show="showSuccessUpload"
         x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center"
         style="background-color: rgba(11, 31, 58, 0.6); backdrop-filter: blur(4px);"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100">
        <div class="rounded-3xl shadow-2xl p-8 max-w-sm w-full mx-4 text-center"
             style="background-color: white;"
             x-transition:enter="transition ease-out duration-300 delay-75"
             x-transition:enter-start="opacity-0 scale-75"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-6">
                <div class="mx-auto w-20 h-20 rounded-full flex items-center justify-center shadow-lg animate-bounce-in"
                     style="background: linear-gradient(to bottom right, #60a5fa, #3b82f6);">
                    <svg class="w-10 h-10" style="color: white;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
            </div>
            <h3 class="text-2xl font-bold mb-2" style="color: #111827;">Uploaded!</h3>
            <p class="text-sm" style="color: #6b7280;">Document has been uploaded successfully</p>
        </div>
    </div>

</div>

<!-- PAU Flyout -->
<div id="pau-flyout" style="
    display:none;
    position:fixed;
    width:230px;
    background:white;
    border:1px solid #c7dcff;
    border-radius:0.625rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    z-index:999999;
    overflow:hidden;
">
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        PAU SUB-UNITS
    </div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, [
            'Resumption NCO', 'TOP NCO', 'Restoration NCO',
            'Prior Years NCO', 'Pension Differential 18-19', 'Own Right NCO'
        ]))
            <div
                class="flyout-item"
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
                style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<!-- BGCU Flyout -->
<div id="bgcu-flyout" style="
    display:none;
    position:fixed;
    width:210px;
    background:white;
    border:1px solid #c7dcff;
    border-radius:0.625rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.15);
    z-index:999999;
    overflow:hidden;
">
    <div style="padding:0.5rem 1rem 0.4rem; font-size:0.7rem; font-weight:700; color:#1e5ba8; background:#f0f6ff; border-bottom:1px solid #c7dcff; letter-spacing:0.05em;">
        BGCU SUB-UNITS
    </div>
    @foreach($units as $subUnit)
        @if(in_array($subUnit->name, [
            'Posthumous NCO', 'Retirement NCO', 'RSAB NCO', 'CDD NCO'
        ]))
            <div
                class="flyout-item"
                data-unit-id="{{ $subUnit->id }}"
                data-unit-name="{{ $subUnit->name }}"
                style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
            >
                {{ $subUnit->name }}
            </div>
        @endif
    @endforeach
</div>

<!-- Doctype Flyout (fixed-position so it escapes modal overflow:auto clipping) -->
<div id="doctype-flyout" style="
    display:none;
    position:fixed;
    background:white;
    border:1px solid #d1d5db;
    border-radius:0.625rem;
    box-shadow:0 8px 24px rgba(0,0,0,0.12);
    z-index:999999;
    overflow:hidden;
    max-height:220px;
    overflow-y:auto;
    min-width:200px;
">
    @foreach($documentTypes as $type)
        <div
            class="doctype-flyout-item"
            data-value="{{ $type->name }}"
            style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s;"
        >
            {{ $type->name }}
        </div>
    @endforeach
    {{-- Always append Others at the bottom --}}
    <div
        class="doctype-flyout-item"
        data-value="Others"
        style="padding:0.6rem 1rem; font-size:0.875rem; color:#374151; cursor:pointer; transition:background 0.15s; border-top:1px solid #f3f4f6;"
    >
        Others
    </div>
</div>

<style>
    [x-cloak] {
        display: none !important;
    }
    @keyframes bounce-in {
        0%   { transform: scale(0); opacity: 0; }
        50%  { transform: scale(1.1); }
        100% { transform: scale(1); opacity: 1; }
    }
    .animate-bounce-in {
        animation: bounce-in 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    }
</style>

<script>
    let flyoutTimers = {};

    // ── Doctype picker (fixed flyout to escape modal overflow clipping) ───────
    function toggleDoctypeDropdown(e) {
        e.stopPropagation();
        const btn    = document.getElementById('doctype-picker-btn');
        const flyout = document.getElementById('doctype-flyout');
        const isOpen = flyout.style.display === 'block';

        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');

        if (isOpen) {
            flyout.style.display = 'none';
            return;
        }

        const rect = btn.getBoundingClientRect();
        flyout.style.width  = rect.width + 'px';
        flyout.style.top    = (rect.bottom + 4) + 'px';
        flyout.style.left   = rect.left + 'px';
        flyout.style.display = 'block';
    }

    function selectDoctype(value) {
        document.getElementById('doctype-hidden-input').value = value;
        const label       = document.getElementById('doctype-picker-label');
        label.textContent = value;
        label.style.color = '#111827';
        document.getElementById('doctype-flyout').style.display = 'none';

        // Show / hide the "Others" free-text field
        const othersWrapper = document.getElementById('doctype-others-wrapper');
        const othersInput   = document.getElementById('doctype-others-input');
        if (value === 'Others') {
            othersWrapper.style.display = 'block';
            othersInput.required = true;
            othersInput.focus();
        } else {
            othersWrapper.style.display = 'none';
            othersInput.required = false;
            othersInput.value   = '';
        }
    }

    // ── Unit picker ──────────────────────────────────────────────────────────
    function toggleUnitDropdown(e) {
        e.stopPropagation();
        const dropdown = document.getElementById('unit-dropdown');
        const isOpen   = dropdown.style.display === 'block';
        dropdown.style.display = isOpen ? 'none' : 'block';
        if (isOpen) {
            hideFlyout('pau-flyout');
            hideFlyout('bgcu-flyout');
        }
        document.getElementById('doctype-flyout').style.display = 'none';
    }

    function selectUnit(id, name) {
        document.getElementById('unit-hidden-input').value = id;
        const label = document.getElementById('unit-picker-label');
        label.textContent = name;
        label.style.color = id ? '#111827' : '#6b7280';
        document.getElementById('unit-dropdown').style.display = 'none';
        hideFlyout('pau-flyout');
        hideFlyout('bgcu-flyout');
    }

    function hideFlyout(id) {
        clearTimeout(flyoutTimers[id]);
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    }

    // ── Init ─────────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        const pauFlyout     = document.getElementById('pau-flyout');
        const bgcuFlyout    = document.getElementById('bgcu-flyout');
        const doctypeFlyout = document.getElementById('doctype-flyout');

        // Move all fixed flyouts to <body> so z-index / overflow is never clipped
        document.body.appendChild(pauFlyout);
        document.body.appendChild(bgcuFlyout);
        document.body.appendChild(doctypeFlyout);

        // ── Doctype flyout items ─────────────────────────────────────────────
        document.querySelectorAll('.doctype-flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#f3f4f6');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click',      () => selectDoctype(item.dataset.value));
        });

        // ── PAU / BGCU flyout items ──────────────────────────────────────────
        document.querySelectorAll('#pau-flyout .flyout-item, #bgcu-flyout .flyout-item').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => selectUnit(item.dataset.unitId, item.dataset.unitName));
        });

        // Keep PAU/BGCU flyouts open while mouse is inside them
        [pauFlyout, bgcuFlyout].forEach(flyout => {
            flyout.addEventListener('mouseenter', () => clearTimeout(flyoutTimers[flyout.id]));
            flyout.addEventListener('mouseleave', () => hideFlyout(flyout.id));
        });

        // ── Filter bar: trigger button ───────────────────────────────────────
        const filterTrigger = document.querySelector('[data-filter-unit-trigger]');
        if (filterTrigger) {
            filterTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                const menu = document.querySelector('[data-filter-unit-menu]');
                menu.classList.toggle('is-hidden');
            });
        }

        // ── Filter bar: option click ─────────────────────────────────────────
        document.querySelectorAll('[data-filter-unit-option]').forEach(option => {
            option.addEventListener('click', () => {
                const id   = option.dataset.unitId;
                const name = option.dataset.unitName;
                document.getElementById('filter-unit-hidden-input').value = id;
                const label = document.getElementById('filter-unit-picker-label');
                label.textContent = name;
                label.style.color = id ? '#111827' : '#6b7280';
                document.querySelector('[data-filter-unit-menu]').classList.add('is-hidden');
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
                document.querySelector('[data-live-search-form]').submit();
            });
        });

        // ── Filter bar flyout items ──────────────────────────────────────────
        document.querySelectorAll('[data-filter-flyout-item]').forEach(item => {
            item.addEventListener('mouseenter', () => item.style.background = '#eff6ff');
            item.addEventListener('mouseleave', () => item.style.background = '');
            item.addEventListener('click', () => {
                document.getElementById('filter-unit-hidden-input').value = item.dataset.unitId;
                const label = document.getElementById('filter-unit-picker-label');
                label.textContent = item.dataset.unitName;
                label.style.color = '#111827';
                document.querySelector('[data-filter-unit-menu]').classList.add('is-hidden');
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
                document.querySelector('[data-live-search-form]').submit();
            });
        });

        // ── Filter bar option hover → show sub-unit flyouts ──────────────────
        document.querySelectorAll('[data-filter-unit-option][data-has-flyout]').forEach(option => {
            option.addEventListener('mouseenter', () => {
                const flyoutKey = option.dataset.hasFlyout;
                const other     = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                hideFlyout(other + '-flyout');
                clearTimeout(flyoutTimers[flyoutKey + '-flyout']);

                const filterFlyout = document.querySelector('[data-filter-flyout="' + flyoutKey + '"]');
                const rect = option.getBoundingClientRect();
                filterFlyout.style.top  = rect.top + 'px';
                filterFlyout.style.left = (rect.right + 4) + 'px';
                filterFlyout.style.display = 'block';
            });
            option.addEventListener('mouseleave', () => {
                const flyoutKey = option.dataset.hasFlyout;
                flyoutTimers[flyoutKey + '-flyout'] = setTimeout(() => {
                    hideFlyout(flyoutKey + '-flyout');
                }, 120);
            });
        });

        // Keep filter bar flyouts open while mouse is inside
        document.querySelectorAll('[data-filter-flyout]').forEach(flyout => {
            flyout.addEventListener('mouseenter', () => {
                const key = flyout.dataset.filterFlyout;
                clearTimeout(flyoutTimers[key + '-flyout']);
            });
            flyout.addEventListener('mouseleave', () => {
                const key = flyout.dataset.filterFlyout;
                flyoutTimers[key + '-flyout'] = setTimeout(() => hideFlyout(flyout.id || flyout), 120);
            });
        });

        // ── Modal unit picker: unit-row hover + click ────────────────────────
        document.querySelectorAll('.unit-row').forEach(row => {
            row.addEventListener('mouseenter', () => {
                row.style.background = '#f3f4f6';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    const other = flyoutKey === 'pau' ? 'bgcu' : 'pau';
                    hideFlyout(other + '-flyout');
                    clearTimeout(flyoutTimers[flyoutKey + '-flyout']);
                    const rect   = row.getBoundingClientRect();
                    const flyout = document.getElementById(flyoutKey + '-flyout');
                    flyout.style.top  = rect.top + 'px';
                    flyout.style.left = (rect.right + 6) + 'px';
                    flyout.style.display = 'block';
                } else {
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');
                }
            });
            row.addEventListener('mouseleave', () => {
                row.style.background = '';
                const flyoutKey = row.dataset.hasFlyout;
                if (flyoutKey) {
                    flyoutTimers[flyoutKey + '-flyout'] = setTimeout(() => hideFlyout(flyoutKey + '-flyout'), 120);
                }
            });
            row.addEventListener('click', () => selectUnit(row.dataset.unitId, row.dataset.unitName));
        });

        // ── Close all dropdowns on outside click ─────────────────────────────
        document.addEventListener('click', function (e) {
            const unitPicker    = document.getElementById('unit-picker');
            const doctypePicker = document.getElementById('doctype-picker');

            if (unitPicker && !unitPicker.contains(e.target) &&
                !pauFlyout.contains(e.target) && !bgcuFlyout.contains(e.target)) {
                document.getElementById('unit-dropdown').style.display = 'none';
                hideFlyout('pau-flyout');
                hideFlyout('bgcu-flyout');
            }

            if (!doctypeFlyout.contains(e.target) &&
                !(doctypePicker && doctypePicker.contains(e.target))) {
                doctypeFlyout.style.display = 'none';
            }

            const filterMenu      = document.querySelector('[data-filter-unit-menu]');
            const filterTriggerEl = document.querySelector('[data-filter-unit-trigger]');
            if (filterMenu && filterTriggerEl &&
                !filterMenu.contains(e.target) && !filterTriggerEl.contains(e.target)) {
                filterMenu.classList.add('is-hidden');
            }
        });

        // ── Reset modal pickers when modal closes ────────────────────────────
        const modalBackdrop = document.querySelector('[x-show="open"]');
        if (modalBackdrop) {
            new MutationObserver(function () {
                if (modalBackdrop.style.display === 'none') {
                    // Reset unit picker
                    document.getElementById('unit-hidden-input').value = '';
                    const unitLabel = document.getElementById('unit-picker-label');
                    unitLabel.textContent = 'Select Receiving Unit';
                    unitLabel.style.color = '#6b7280';
                    document.getElementById('unit-dropdown').style.display = 'none';
                    hideFlyout('pau-flyout');
                    hideFlyout('bgcu-flyout');

                    // Reset doctype picker
                    document.getElementById('doctype-hidden-input').value = '';
                    const doctypeLabel = document.getElementById('doctype-picker-label');
                    doctypeLabel.textContent = 'Select document type';
                    doctypeLabel.style.color = '#6b7280';
                    doctypeFlyout.style.display = 'none';

                    // Reset Others field
                    document.getElementById('doctype-others-wrapper').style.display = 'none';
                    document.getElementById('doctype-others-input').value   = '';
                    document.getElementById('doctype-others-input').required = false;
                }
            }).observe(modalBackdrop, { attributes: true, attributeFilter: ['style'] });
        }
    });
</script>

@endsection
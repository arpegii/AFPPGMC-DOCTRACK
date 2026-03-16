<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OutgoingController extends Controller
{
    /**
     * Display outgoing pending documents sent by user's unit.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $searchQuery = trim((string) $request->input('search', ''));
        $allowedPerPage = [10, 25, 50, 100];
        $perPage = (int) $request->integer('per_page', 10);
        if (!in_array($perPage, $allowedPerPage, true)) {
            $perPage = 10;
        }
        $selectedUnitId = null;
        // Make filter units available to all users
        $filterUnits = $user->isAdmin() ? Unit::all() : Unit::visibleToUser($user);

        // Handle unit filtering for both admins and regular users
        if ($request->has('unit_id')) {
            $selectedUnitId = $request->input('unit_id');
            if ($selectedUnitId) {
                $request->session()->put('unit_filter_id', $selectedUnitId);
            } else {
                $request->session()->forget('unit_filter_id');
            }
        } else {
            $selectedUnitId = $request->session()->get('unit_filter_id');
        }

        // Get units visible to the current user (excludes ADMN for non-admins)
        $units = Unit::visibleToUser($user);

        // Get all document types from the database
        $documentTypes = DocumentType::orderBy('name')->get();

        if ($user->isAdmin()) {
            // Admin sees all pending outgoing documents.
            $query = Document::with(['senderUnit', 'receivingUnit'])
                ->where('status', 'incoming');

            if ($selectedUnitId) {
                $query->where(function ($subQuery) use ($selectedUnitId) {
                    $subQuery->where('sender_unit_id', $selectedUnitId)
                        ->orWhere('receiving_unit_id', $selectedUnitId);
                });
            }

            if ($searchQuery !== '') {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('document_number', 'like', "%{$searchQuery}%")
                        ->orWhere('title', 'like', "%{$searchQuery}%")
                        ->orWhere('document_type', 'like', "%{$searchQuery}%")
                        ->orWhereHas('senderUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('receivingUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        } else {
            // Users see pending documents sent by their unit.
            $query = Document::with(['senderUnit', 'receivingUnit'])
                ->where('sender_unit_id', $user->unit_id)
                ->where('status', 'incoming');

            // Apply unit filter for regular users (by receiving unit)
            if ($selectedUnitId) {
                $query->where('receiving_unit_id', $selectedUnitId);
            }

            if ($searchQuery !== '') {
                $query->where(function ($subQuery) use ($searchQuery) {
                    $subQuery->where('document_number', 'like', "%{$searchQuery}%")
                        ->orWhere('title', 'like', "%{$searchQuery}%")
                        ->orWhere('document_type', 'like', "%{$searchQuery}%")
                        ->orWhereHas('senderUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        })
                        ->orWhereHas('receivingUnit', function ($unitQuery) use ($searchQuery) {
                            $unitQuery->where('name', 'like', "%{$searchQuery}%");
                        });
                });
            }

            $documents = $query->orderBy('created_at', 'desc')
                ->paginate($perPage)
                ->withQueryString();
        }

        return view('outgoing.outgoing', compact(
            'documents',
            'units',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'  // <-- added
        ));
    }
}
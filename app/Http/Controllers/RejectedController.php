<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RejectedController extends Controller
{
    /**
     * Display rejected documents
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

        // Get all units for dropdowns or safe display in view
        $units = Unit::visibleToUser($user);

        // Get all document types from the database
        $documentTypes = DocumentType::orderBy('name')->get();

        if ($user->isAdmin()) {
            // Admin sees all rejected documents
            $query = Document::with(['senderUnit', 'receivingUnit'])
                ->where('status', 'rejected');

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
            // Users see rejected documents sent by their unit.
            $query = Document::with(['senderUnit', 'receivingUnit'])
                ->where('status', 'rejected')
                ->where('sender_unit_id', $user->unit_id);

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

        // Pass both documents and units to the view
        return view('rejected.rejected', compact(
            'documents',
            'units',
            'filterUnits',
            'selectedUnitId',
            'documentTypes'  // <-- added
        ));
    }
}
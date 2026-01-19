<?php

namespace App\Http\Controllers;

use App\Models\PpidRequest;
use App\Notifications\PpidRequestReceived;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class PpidController extends Controller
{
    /**
     * Display PPID information page.
     */
    public function index()
    {
        return Inertia::render('ppid/index');
    }

    /**
     * Display PPID request form.
     */
    public function create()
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return Inertia::render('ppid/form');
    }

    /**
     * Store a new PPID request.
     */
    public function store(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $validated = Validator::make($request->all(), [
            'applicant_name' => 'required|string|max:255',
            'nik' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'email' => 'required|email|max:255',
            'request_type' => 'required|in:informasi_publik,keberatan,sengketa,lainnya',
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:50',
            'priority' => 'required|in:normal,high',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ])->validate();

        $requestNumber = PpidRequest::generateRequestNumber();

        $attachments = [];
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store('ppid-attachments', 'public');
                $attachments[] = $path;
            }
        }

        $ppidRequest = PpidRequest::create([
            'request_number' => $requestNumber,
            'applicant_name' => $validated['applicant_name'],
            'nik' => $validated['nik'] ?? null,
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'],
            'request_type' => $validated['request_type'],
            'subject' => $validated['subject'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'attachments' => $attachments,
            'status' => 'submitted',
        ]);

        // Send notification to user
        $ppidRequest->notify(new PpidRequestReceived($ppidRequest));

        return redirect()->route('ppid.tracking', ['number' => $requestNumber])
            ->with('success', 'PPID request submitted successfully. Your request number is: '.$requestNumber);
    }

    /**
     * Track PPID request status.
     */
    public function tracking(Request $request)
    {
        $requestNumber = $request->query('number');
        $ppidRequest = null;
        $error = null;

        if ($requestNumber) {
            $ppidRequest = PpidRequest::where('request_number', $requestNumber)
                ->first();

            if (! $ppidRequest) {
                $error = 'Request not found. Please check your request number.';
            }
        }

        return Inertia::render('ppid/tracking', [
            'requestNumber' => $requestNumber,
            'request' => $ppidRequest,
            'error' => $error,
        ]);
    }

    /**
     * Display user's PPID requests.
     */
    public function myRequests(Request $request)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $email = $user->email;

        $requests = PpidRequest::where('email', $email)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('ppid/my-requests', [
            'requests' => $requests,
        ]);
    }

    /**
     * Display single PPID request details.
     */
    public function show(string $id)
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();
        $ppidRequest = PpidRequest::where('id', $id)
            ->where('email', $user->email)
            ->firstOrFail();

        // Additional authorization check
        if ($ppidRequest->email !== $user->email) {
            abort(403, 'Unauthorized access to this request');
        }

        return Inertia::render('ppid/show', [
            'request' => $ppidRequest,
        ]);
    }
}

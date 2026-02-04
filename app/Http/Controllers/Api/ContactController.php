<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ContactSubmissionResource;
use App\Http\Requests\StoreContactRequest;
use App\Models\ContactSubmission;
use App\Services\ContactSubmissionService;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    public function __construct(
        private readonly ContactSubmissionService $service,
    ) {}

    /**
     * Submit a contact form.
     */
    public function store(StoreContactRequest $request): JsonResponse
    {
        $submission = $this->service->createSubmission(
            $request->validated(),
            $request->ip(),
            $request->getLocale()
        );

        return response()->json([
            'message' => 'Your message has been received. We will get back to you soon.',
            'submission_id' => $submission->id,
        ], 201);
    }

    /**
     * Get contact submission (admin only).
     */
    public function show(string $id): ContactSubmissionResource|JsonResponse
    {
        $submission = ContactSubmission::find($id);

        if (!$submission) {
            return response()->json([
                'message' => 'Contact submission not found.',
            ], 404);
        }

        return new ContactSubmissionResource($submission);
    }

    /**
     * Update contact submission status (admin only).
     */
    public function update(string $id, string $status): JsonResponse
    {
        $submission = ContactSubmission::find($id);

        if (!$submission) {
            return response()->json([
                'message' => 'Contact submission not found.',
            ], 404);
        }

        if (!in_array($status, ['new', 'in_progress', 'resolved', 'spam'])) {
            return response()->json([
                'message' => 'Invalid status.',
            ], 422);
        }

        $submission->update(['status' => $status]);

        return response()->json([
            'message' => 'Contact submission updated.',
            'submission' => new ContactSubmissionResource($submission),
        ]);
    }
}

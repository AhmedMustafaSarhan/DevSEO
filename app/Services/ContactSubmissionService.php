<?php

namespace App\Services;

use App\Models\ContactSubmission;
use Illuminate\Database\Eloquent\Collection;

class ContactSubmissionService
{
    /**
     * Create a new contact submission.
     */
    public function createSubmission(array $validated, string $ipAddress, string $locale): ContactSubmission
    {
        return ContactSubmission::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'region' => $validated['region'],
            'ip_address' => $ipAddress,
            'status' => 'new',
            'locale' => $locale,
        ]);
    }

    /**
     * Get unread submissions (admin only).
     */
    public function getUnreadSubmissions(): Collection
    {
        return ContactSubmission::where('status', 'new')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get submissions by region.
     */
    public function getByRegion(string $region): Collection
    {
        return ContactSubmission::where('region', $region)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Mark submission as responded.
     */
    public function markAsResponded(ContactSubmission $submission, string $responseMessage): ContactSubmission
    {
        $submission->update([
            'status' => 'resolved',
            'response_message' => $responseMessage,
            'responded_at' => now(),
        ]);

        return $submission;
    }

    /**
     * Delete spam submissions (batch).
     */
    public function deleteSpam(): int
    {
        return ContactSubmission::where('status', 'spam')->delete();
    }
}

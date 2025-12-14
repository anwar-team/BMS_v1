<?php

namespace App\Http\Controllers;

use App\Models\FeedbackComplaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FeedbackComplaintController extends Controller
{
    /**
     * Display a listing of the resource (Admin Only).
     */
    public function index(Request $request)
    {
        $query = FeedbackComplaint::query()->orderBy('created_at', 'desc');

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Search in subject and message
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('subject', 'LIKE', "%{$search}%")
                  ->orWhere('message', 'LIKE', "%{$search}%");
            });
        }

        $feedbacks = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => FeedbackComplaint::count(),
            'pending' => FeedbackComplaint::pending()->count(),
            'in_progress' => FeedbackComplaint::inProgress()->count(),
            'resolved' => FeedbackComplaint::resolved()->count(),
            'feedbacks' => FeedbackComplaint::feedback()->count(),
            'complaints' => FeedbackComplaint::complaint()->count(),
        ];

        return view('admin.feedback.index', compact('feedbacks', 'stats'));
    }

    /**
     * Store a newly created resource in storage (Public).
     */
    public function store(Request $request)
    {
        // Validation
        $validator = Validator::make($request->all(), [
            'type' => 'required|in:feedback,complaint',
            'subject' => 'required|string|min:3|max:255',
            'message' => 'required|string|min:10',
        ], [
            'type.required' => 'يرجى اختيار نوع الرسالة',
            'type.in' => 'نوع الرسالة غير صحيح',
            'subject.required' => 'يرجى إدخال الموضوع',
            'subject.min' => 'الموضوع يجب أن يكون 3 أحرف على الأقل',
            'subject.max' => 'الموضوع يجب ألا يتجاوز 255 حرف',
            'message.required' => 'يرجى إدخال محتوى الرسالة',
            'message.min' => 'الرسالة يجب أن تكون 10 أحرف على الأقل',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        // Create feedback/complaint
        $feedback = FeedbackComplaint::create([
            'type' => $request->type,
            'subject' => $request->subject,
            'message' => $request->message,
            'status' => 'pending',
            'priority' => 'medium',
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم إرسال رسالتك بنجاح! شكراً لك على ملاحظاتك.',
                'data' => $feedback
            ], 201);
        }

        return back()->with('success', 'تم إرسال رسالتك بنجاح! شكراً لك على ملاحظاتك.');
    }

    /**
     * Display the specified resource (Admin Only).
     */
    public function show($id)
    {
        $feedback = FeedbackComplaint::findOrFail($id);
        return view('admin.feedback.show', compact('feedback'));
    }

    /**
     * Show the form for editing the specified resource (Admin Only).
     */
    public function edit($id)
    {
        $feedback = FeedbackComplaint::findOrFail($id);
        return view('admin.feedback.edit', compact('feedback'));
    }

    /**
     * Update the specified resource in storage (Admin Only).
     */
    public function update(Request $request, $id)
    {
        $feedback = FeedbackComplaint::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,in_progress,resolved',
            'priority' => 'required|in:low,medium,high',
            'admin_notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'errors' => $validator->errors()
                ], 422);
            }
            return back()->withErrors($validator)->withInput();
        }

        $feedback->update([
            'status' => $request->status,
            'priority' => $request->priority,
            'admin_notes' => $request->admin_notes,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'تم تحديث البيانات بنجاح',
                'data' => $feedback
            ]);
        }

        return back()->with('success', 'تم تحديث البيانات بنجاح');
    }

    /**
     * Remove the specified resource from storage (Admin Only).
     */
    public function destroy($id)
    {
        $feedback = FeedbackComplaint::findOrFail($id);
        $feedback->delete();

        return back()->with('success', 'تم حذف الرسالة بنجاح');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /**
     * Fetch paginated reviews (5 per page)
     */
    public function index()
    {
        $reviews = Review::where('is_approved', true)
            ->orderBy('created_at', 'desc')
            ->paginate(5);
            
        return response()->json($reviews);
    }
    
    /**
     * Store new review
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'reviewer_name' => 'required|string|max:200',
            'rating' => 'required|integer|min:1|max:5',
            'review_text' => 'required|string|max:1000'
        ]);
        
        // Trim whitespace
        $validated['reviewer_name'] = trim($validated['reviewer_name']);
        $validated['review_text'] = trim($validated['review_text']);
        
        $review = Review::create($validated);
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for your review!',
            'review' => $review
        ], 201);
    }
}
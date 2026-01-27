<?php

namespace App\Http\Controllers;

use App\Models\SharedFile;
use App\Services\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SharedDriveController extends Controller
{
    public function index()
    {
        $files = SharedFile::with('user')->latest()->get();
        return view('drive.index', compact('files'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'description' => 'nullable|string|max:1000',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            $path = $file->storeAs('shared_drive', $file->hashName(), [
                'disk' => config('filesystems.default'), // Uses 's3' or 'public' based on .env
                'visibility' => 'public',
            ]);

            $sharedFile = SharedFile::create([
                'user_id' => Auth::id(),
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);

            // Create Activity Log
            LogActivity::record('file_uploaded', "Uploaded file: {$filename}", $sharedFile);

            return response()->json($sharedFile->load('user'), 201);
        }

        return response()->json(['error' => 'File not found'], 400);
    }

    public function destroy($id)
    {
        // Optional: Policy check (only owner or admin)
        // if ($file->user_id !== Auth::id()) { abort(403); }

        $file = SharedFile::findOrFail($id);

        if (Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }

        // Create Activity Log before deletion
        LogActivity::record('file_deleted', "Deleted file: {$file->filename}", $file);

        $file->delete();

        return response()->json(['success' => true]);
    }
}

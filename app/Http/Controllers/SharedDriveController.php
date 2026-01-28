<?php

namespace App\Http\Controllers;

use App\Models\SharedFile;
use App\Services\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SharedDriveController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $folderId = $request->input('folder_id');
        
        $query = SharedFile::with('user');

        if ($search) {
            // Search globally
            $query->where('filename', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
        } else {
            // Filter by folder
            if ($folderId) {
                $query->where('parent_id', $folderId);
            } else {
                $query->whereNull('parent_id');
            }
        }

        // Sort: Folders first, then By Date Desc
        $query->orderByDesc('is_folder')->latest();

        $files = $query->paginate(20)->withQueryString();

        // Breadcrumbs Logic
        $breadcrumbs = [];
        $currentFolder = null;

        if ($folderId && !$search) {
            $currentFolder = SharedFile::find($folderId);
            $temp = $currentFolder;
            while ($temp) {
                array_unshift($breadcrumbs, $temp);
                $temp = $temp->parent;
            }
        }

        return view('drive.index', compact('files', 'breadcrumbs', 'currentFolder', 'search'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // 100MB max
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:shared_files,id'
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $file->getClientOriginalName();
            
            // Generate path. Ideally use folder structure in S3 too, but flat is fine for now or simpler.
            // Let's use flat structure "shared_drive/{hash}" to avoid S3 folder complexity for now,
            // or we could append parent path? Flat is safer for moving files later.
            $path = $file->storeAs('shared_drive', $file->hashName(), [
                'disk' => config('filesystems.default'),
                'visibility' => 'public',
            ]);

            $sharedFile = SharedFile::create([
                'user_id' => Auth::id(),
                'parent_id' => $request->parent_id,
                'filename' => $filename,
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
                'is_folder' => false,
            ]);

            LogActivity::record('file_uploaded', "Uploaded file: {$filename}", $sharedFile);

            return response()->json($sharedFile->load('user'), 201);
        }

        return response()->json(['error' => 'File not found'], 400);
    }

    public function createFolder(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:shared_files,id'
        ]);

        $folder = SharedFile::create([
            'user_id' => Auth::id(),
            'parent_id' => $request->parent_id,
            'filename' => $request->name,
            'file_path' => '', // Empty for folders
            'file_type' => 'folder',
            'file_size' => 0,
            'description' => null,
            'is_folder' => true,
        ]);

        LogActivity::record('folder_created', "Created folder: {$request->name}", $folder);

        return response()->json($folder, 201);
    }

    public function destroy($id)
    {
        $file = SharedFile::findOrFail($id);
        
        // If query param 'permanent' is needed? For now just cascading delete via DB.
        
        // If it's a file, delete from S3
        if (!$file->is_folder && Storage::exists($file->file_path)) {
            Storage::delete($file->file_path);
        }
        
        // If it's a folder, we depend on DB Cascade (defined in migration) to remove children rows.
        // BUT we must delete physical files of children first if we want to clean S3.
        // Recursive deletion is heavy. For now, assuming standard usage, user deletes content first or we implement a background job.
        // Given complexity, I'll add a simple recursive file deletion here.
        if ($file->is_folder) {
            $this->deleteFolderContents($file);
        }

        $logAction = $file->is_folder ? 'folder_deleted' : 'file_deleted';
        LogActivity::record($logAction, "Deleted: {$file->filename}", $file);

        $file->delete();

        return response()->json(['success' => true]);
    }

    private function deleteFolderContents($folder) {
        foreach ($folder->children as $child) {
            if ($child->is_folder) {
                $this->deleteFolderContents($child);
            } else {
                if (Storage::exists($child->file_path)) {
                    Storage::delete($child->file_path);
                }
            }
        }
    }

    public function getFolders()
    {
        // Return all folders for the move dialog
        // Can optimize to exclude current descendants if needed, but client-side filtering or server-side valid check is fine.
        $folders = SharedFile::where('is_folder', true)
            ->select('id', 'filename', 'parent_id', 'created_at')
            ->orderBy('filename')
            ->get();
            
        return response()->json($folders);
    }

    public function move(Request $request, $id)
    {
        $request->validate([
            'target_folder_id' => 'nullable|exists:shared_files,id',
        ]);

        $file = SharedFile::findOrFail($id);
        $targetId = $request->target_folder_id;

        // Validation: Cannot move folder into itself or its own subfolder
        if ($file->is_folder && $targetId) {
            if ($file->id == $targetId) {
                return response()->json(['message' => 'Cannot move folder into itself.'], 422);
            }

            if ($this->isDescendant($targetId, $file->id)) {
                return response()->json(['message' => 'Cannot move folder into its own subfolder.'], 422);
            }
        }

        $oldParentName = $file->parent ? $file->parent->filename : 'Home';
        $file->update(['parent_id' => $targetId]);
        $newParent = $targetId ? SharedFile::find($targetId) : null;
        $newParentName = $newParent ? $newParent->filename : 'Home';

        LogActivity::record('file_moved', "Moved {$file->filename} from {$oldParentName} to {$newParentName}", $file);

        return response()->json($file);
    }

    private function isDescendant($targetId, $folderId)
    {
        // Check if targetId is a descendant of folderId
        $cursor = SharedFile::find($targetId);
        while ($cursor) {
            if ($cursor->parent_id == $folderId) {
                return true;
            }
            $cursor = $cursor->parent; 
        }
        return false;
    }
}

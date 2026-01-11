<?php

namespace App\Http\Controllers;

use App\Models\Credential;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CredentialController extends Controller
{
    /**
     * Display a list of credentials.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $credentials = Credential::with('accessList')->get();
            $users = User::where('role', '!=', 'admin')->get(); // For sharing modal
        } else {
            $credentials = $user->sharedCredentials;
            $users = collect(); // Regular users can't share
        }

        return view('credentials.index', compact('credentials', 'users'));
    }

    /**
     * Store a newly created credential.
     */
    public function store(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'project_name' => 'required|string|max:255',
            'service_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255',
            'password' => 'required|string',
            'description' => 'nullable|string',
        ]);

        Credential::create([
            ...$validated,
            'created_by' => Auth::id(),
        ]);

        return back()->with('success', 'Credential added successfully.');
    }

    /**
     * Share credentials with users.
     */
    public function share(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'credential_ids' => 'required|array',
            'credential_ids.*' => 'exists:credentials,id',
            'user_ids' => 'array', // Empty means revoke all
            'user_ids.*' => 'exists:users,id',
        ]);

        $credentials = Credential::whereIn('id', $validated['credential_ids'])->get();
        $users = $validated['user_ids'] ?? [];

        foreach ($credentials as $credential) {
            $credential->accessList()->sync($users);
        }

        return back()->with('success', 'Sharing settings updated.');
    }

    /**
     * Remove the specified credential.
     */
    public function destroy(Credential $credential)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $credential->delete();

        return back()->with('success', 'Credential deleted.');
    }

    /**
     * Remove multiple credentials.
     */
    public function bulkDestroy(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'credential_ids' => 'required|array',
            'credential_ids.*' => 'exists:credentials,id',
        ]);

        Credential::whereIn('id', $validated['credential_ids'])->delete();

        return back()->with('success', 'Selected credentials deleted.');
    }
}

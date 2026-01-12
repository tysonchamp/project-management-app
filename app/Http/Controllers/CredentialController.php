<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Credential;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

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
            // Show shared credentials OR credentials created by this user
            $credentials = Credential::where('created_by', $user->id)
                ->orWhereHas('accessList', function($q) use ($user) {
                    $q->where('users.id', $user->id);
                })->get();
            $users = collect(); // Regular users can't share
        }

        return view('credentials.index', compact('credentials', 'users'));
    }

    /**
     * Store a newly created credential.
     */
    public function store(Request $request)
    {
        try {
            // Allowed for all authenticated users
            $validated = $request->validate([
                'project_name' => 'required|string|max:255',
                'service_name' => 'required|string|max:255',
                'username' => 'nullable|string|max:255',
                'password' => 'required|string',
                'description' => 'nullable|string',
            ]);


            DB::transaction(function () use ($validated) {
                $credential = Credential::create([
                    ...$validated,
                    'created_by' => Auth::id(),
                ]);

                \App\Services\LogActivity::record(
                    'create_credential', 
                    "Created credential for {$credential->project_name} - {$credential->service_name}", 
                    $credential
                );
            });

            return back()->with('success', 'Credential added successfully.');

        } catch (ValidationException $th) {
            Log::error('Task creation failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return redirect()->back()->withInput()->with('error', $th->validator->errors()->first());

        } catch (\Throwable $th) {
            Log::error('credential creation failed', [
                'error' => $th->getMessage(),
                'trace' => $th->getTraceAsString(),
                'user_id' => Auth::id(),
            ]);

            return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Share credentials with users.
     */
    public function share(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            abort(403);
        }

        try {
            $validated = $request->validate([
                'credential_ids' => 'required|array',
                'credential_ids.*' => 'exists:credentials,id',
                'user_ids' => 'array', // Empty means revoke all
                'user_ids.*' => 'exists:users,id',
            ]);

            DB::transaction(function () use ($validated) {
                $credentials = Credential::whereIn('id', $validated['credential_ids'])->get();
                $users = $validated['user_ids'] ?? [];

                foreach ($credentials as $credential) {
                    $credential->accessList()->sync($users);
                    
                    \App\Services\LogActivity::record(
                        'share_credential', 
                        "Updated sharing settings for credential {$credential->id}", 
                        $credential
                    );
                }
            });

            return back()->with('success', 'Successfully sharing settings updated.');

        } catch (ValidationException $th) {
            return redirect()->back()->with('error', $th->validator->errors()->first());
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }

    /**
     * Remove the specified credential.
     */
    public function destroy(Credential $credential)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && $credential->created_by !== $user->id) {
            abort(403);
        }

        try {
            DB::transaction(function () use ($credential) {
                \App\Services\LogActivity::record(
                    'delete_credential', 
                    "Deleted credential for {$credential->project_name}", 
                    $credential
                );

                $credential->delete();
            });

            return back()->with('success', 'Successfully credential deleted.');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }

        
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

        try {
            DB::transaction(function () use ($validated) {
                Credential::whereIn('id', $validated['credential_ids'])->delete();
            });

            return back()->with('success', 'Successfully selected credentials deleted.');

        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong. Please try again.');
        }
    }
}

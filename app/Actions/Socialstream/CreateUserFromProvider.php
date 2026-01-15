<?php

namespace App\Actions\Socialstream;

use App\Http\Controllers\GroupController;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use JoelButcher\Socialstream\Contracts\CreatesConnectedAccounts;
use JoelButcher\Socialstream\Contracts\CreatesUserFromProvider;
use JoelButcher\Socialstream\Socialstream;
use Laravel\Socialite\Contracts\User as ProviderUser;

class CreateUserFromProvider implements CreatesUserFromProvider
{
    /**
     * The creates connected accounts instance.
     */
    public CreatesConnectedAccounts $createsConnectedAccounts;

    public GroupController $groupController;

    /**
     * Create a new action instance.
     */
    public function __construct(CreatesConnectedAccounts $createsConnectedAccounts, GroupController $groupController)
    {
        $this->createsConnectedAccounts = $createsConnectedAccounts;
        $this->groupController = $groupController;
    }

    /**
     * Create a new user from a social provider user.
     */
    public function create(string $provider, ProviderUser $providerUser): User
    {
        // Check if user was previously deleted
        if (User::withTrashed()->where('email', $providerUser->getEmail())->exists()) {
            $user = User::withTrashed()->where('email', $providerUser->getEmail())->first();
            if (isset($user) && $user->trashed()) {
                $user->restore();

                return $user;
            }
        }

        return DB::transaction(function () use ($provider, $providerUser) {
            return tap(User::create([
                'name' => $providerUser->getName() ?? $providerUser->getNickname(),
                'email' => $providerUser->getEmail(),
            ]), function (User $user) use ($provider, $providerUser) {
                $user->markEmailAsVerified();

                if (Socialstream::hasProviderAvatarsFeature() && $providerUser->getAvatar()) {
                    $user->setProfilePhotoFromUrl($providerUser->getAvatar());
                }

                // Link the user to any existing group memberships by email
                $groupMembers = $this->groupController->getGroupMembersByEmail($user->email);
                foreach ($groupMembers as $m) {
                    if (! isset($m->user_id)) {
                        $m->update(['user_id' => $user->id]);
                    }
                }

                $this->createsConnectedAccounts->create($user, $provider, $providerUser);
            });
        });
    }
}

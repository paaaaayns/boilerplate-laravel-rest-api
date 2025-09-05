<?php

namespace App\Imports;

use App\Models\Profile;
use App\Models\User;
use App\Enums\GenderEnum;
use App\Enums\RoleEnum;
use App\Rules\LettersAndSpaceOnly;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;

class UsersFirstSheetImport implements
    ToCollection,
    WithHeadingRow,
    WithValidation,
    SkipsEmptyRows
{
    use Importable;

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $existingUser = User::withTrashed()->where('email', $row['email'])->first();

            if ($existingUser && $existingUser->trashed()) {
                throw new ModelNotFoundException("Cannot insert record, user with email {$row['email']} is soft-deleted.");
            }

            User::updateOrCreate(
                ['email' => $row['email']],
                [
                    'email' => $row['email'],
                    'updated_at' => now()
                ],
            );

            $user = User::where(
                'email',
                $row['email']
            )->firstOrFail();

            Profile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'user_id' => $user->id,
                    'first_name' => $row['first_name'],
                    'middle_name' => $row['middle_name'] ?? null,
                    'last_name' => $row['last_name'],
                    'gender' => $row['gender'] ?? null,
                    'contact_number' => $row['contact_number'] ?? null,
                ]
            );
            $user->syncRoles([RoleEnum::User->value]);
        }
    }

    public function rules(): array
    {
        return [
            'email' => [
                'required',
                'email:rfc,strict,dns,sppof,filter,filter_unicode',
            ],

            'first_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                new LettersAndSpaceOnly()
            ],

            'middle_name' => [
                'nullable',
                'string',
                'max:255',
                new LettersAndSpaceOnly()
            ],

            'last_name' => [
                'required',
                'string',
                'min:2',
                'max:255',
                new LettersAndSpaceOnly()
            ],

            'contact_number' => [
                'nullable',
                'string',
            ],

            'gender' => [
                'nullable',
                Rule::in(
                    array_map(
                        fn($case) => $case->value,
                        GenderEnum::cases()
                    )
                ),
            ],
        ];
    }
}

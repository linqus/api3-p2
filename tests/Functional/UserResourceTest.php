<?php

namespace App\Tests\Functional;

use App\Factory\UserFactory;
use Zenstruck\Foundry\Test\ResetDatabase;

class UserResourceTest extends ApiTestCase
{
    use ResetDatabase; 

    public function testPostToCreateUser(): void
    {
        $this->browser()
            ->post('/api/users', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'username' => 'draggin_in_the_morning',
                    'password' => 'password',
                ]
            ])
            ->assertStatus(201)
            ->post('/login', [
                'json' => [
                    'email' => 'draggin_in_the_morning@coffee.com',
                    'password' => 'password',
                ]
            ])
            ->assertSuccessful()
        ;
    }

    public function testPatchToUpdateUser(): void
    {

        $user = UserFactory::createOne([
            'username' => 'elon1',
            'password' => 'passw2',
        ]);

        $this->browser()
            ->actingAs($user)
            ->patch('/api/users/' . $user->getId(), [
                'json' => [
                    'username' => 'changed',
                ],
                'headers' => [
                    'Content-Type' => 'application/merge-patch+json',
                ],
            ])
            ->assertStatus(200)

            ->post('/login', [
                'json' => [
                    'username' => 'elon1',
                    'password' => 'passw2',
                ]
            ])
            ->assertSuccessful()

        ;
    }
}

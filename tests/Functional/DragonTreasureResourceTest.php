<?php

namespace App\Tests\Functional;

use App\Entity\ApiToken;
use App\Factory\ApiTokenFactory;
use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Zenstruck\Browser\HttpOptions;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends ApiTestCase
{
    use ResetDatabase;

    public function testGetCollectionOfTreasures(): void
    {

        DragonTreasureFactory::createMany(5);

        $json = $this->browser()
            ->get('/api/treasures')
            ->assertJson()
            ->assertJsonMatches('"hydra:totalItems"',5)
            ->json()
        ;
    
        $this->assertSame(
            array_keys($json->decoded()["hydra:member"][0]),
            [  
                0 => "@id",
                1 => "@type",
                2 => "name",
                3 => "description",
                4 => "value",
                5 => "coolFactor",
                6 => "owner",
                7 => "shortDescription",
                8 => "plunderedAtAgo",
            ]
        );
    }
    
    public function testPostToCreateTreasure(): void
    {
        $user = UserFactory::createOne();
        $this->browser()
            ->actingAs($user)
            ->post('/api/treasures', [
                'json' => [],
            ])
            ->assertStatus(422)
            ->post('/api/treasures', [
                'json'=>[
                    'name' => 'A thing',
                    'description' => 'nice to have thing',
                    'value' => 1000,
                    'coolFactor' => 1,
                    'owner' => '/api/users/'.$user->getId(),
                ]
            ])
            ->assertStatus(201)
            ->assertJsonMatches('name','A thing')

        ;
    }

    public function testPostToCreateTreasureWithApi(): void
    {
        $user = UserFactory::createOne();
        $apiToken = ApiTokenFactory::createOne([
            'scope' => [
                'ROLE_TREASURE_CREATE',
            ],
            'ownedBy' => $user,
        ]);
        //dd($user);
        //dd($apiToken);

        $this->browser()
            ->post('/api/treasures',[
                'json'=>[
                    'name' => 'A thingy',
                    'description' => 'nice to have thing',
                    'value' => 1000,
                    'coolFactor' => 1,
                    'owner' => '/api/users/'.$user->getId(),
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken->getToken(),
                ],
            ])
            ->assertStatus(201)
            ->assertJsonMatches('name','A thingy')
        ;
    }

    public function testPatchToUpdateTreasure(): void
    {
        $user1 = UserFactory::createOne();
        $user2 = UserFactory::createOne();

        $treasure1 = DragonTreasureFactory::createOne([
            'owner' => $user1,
        ]);
        $treasure2 = DragonTreasureFactory::createOne([
            'owner' => $user1,
        ]);

        $this->browser()
            ->actingAs($user1)
            ->patch(
                '/api/treasures/'. $treasure1->getId(), [
                    'json' => [
                        'value'=>12345
                    ],
                ]
            )
            ->assertStatus(200)
            ->assertJsonMatches('value',12345)


            ->actingAs($user2)
            ->patch(
                '/api/treasures/'. $treasure2->getId(), [
                    'json' => [
                        'value'=>2345
                    ],
                ]
            )
            ->assertStatus(403)

            ->actingAs($user1)
            ->patch(
                '/api/treasures/'. $treasure2->getId(), [
                    'json' => [
                        'owner'=>'/api/users/'.$user2->getId()
                    ],
                ]
            )
            ->assertStatus(403)
        ;

    }

    public function testAdminUserCanPatchToEditTreasure(): void
    {
        $admin = UserFactory::new()->asAdmin()->create();
        $treasure = DragonTreasureFactory::createOne([
            'value'=>12345,
        ]);

        $this->browser()
            ->actingAs($admin)
            ->patch('/api/treasures/'.$treasure->getId(),[
                'json' => [
                    'value' => 234,
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('value',234)

            ->patch('/api/treasures/'.$treasure->getId(),[
                'json' => [
                    'owner' => '/api/users/'.$admin->getId(),
                ]
            ])
            ->assertStatus(200)
            ->assertJsonMatches('owner','/api/users/'.$admin->getId())
        ;
    }
}
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
            ->dump()
        ;
    }
}
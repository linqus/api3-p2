<?php

namespace App\Tests\Functional;

use App\Factory\DragonTreasureFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;
use Zenstruck\Foundry\Test\ResetDatabase;

class DragonTreasureResourceTest extends KernelTestCase
{
    use HasBrowser;
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
                'json' => [
                    'name' => 'A thing',
                    'description' => 'nice to have thing',
                    'value' => 1000,
                    'coolFactor' => 1,
                    'owner' => '/api/users/'.$user->getId(),
                ],
            ])
            ->assertStatus(201)
            ->dump()
            ->assertJsonMatches('name','A thing')

        ;
    }
}
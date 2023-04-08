<?php

namespace App\Tests\Functional;

use App\Entity\DragonTreasure;
use App\Factory\DragonTreasureFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Json;
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
}
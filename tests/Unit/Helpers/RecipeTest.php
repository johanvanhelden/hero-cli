<?php

declare(strict_types=1);

namespace Tests\Unit\Helpers;

use App\Helpers\Recipe;
use Illuminate\Support\Facades\App;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    /** @test */
    public function it_can_replace_variables(): void
    {
        $this->assertEquals(
            '/home/test/projects/test-project/else',
            Recipe::processVariables('{localProjectPath}/else', 'test-project')
        );
    }

    /** @test */
    public function it_can_load_the_recipes_list(): void
    {
        App::shouldReceive('basePath')
            ->andReturn(base_path('tests/__fixtures__/recipes.yml'));

        $recipes = Recipe::getRecipesList();

        $this->assertEquals(
            ['composer', 'assets', 'update', 'vue'],
            array_keys($recipes)
        );
    }
}

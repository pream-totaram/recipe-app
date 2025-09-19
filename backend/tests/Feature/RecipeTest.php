<?php

namespace Tests\Feature;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RecipeTest extends TestCase
{
    use RefreshDatabase;
    private User $user;
    private string $uriPrefix = '/api/recipes';
    private array $recipeData = [
        'title' => 'Test Recipe',
        'description' => 'A test recipe description',
        'instructions' => 'Mix ingredients and cook',
        'prep_time' => 15,
        'cook_time' => 30,
        'servings' => 4,
        'difficulty' => 'easy',
        'cuisine_type' => 'Italian',
        'is_public' => 1,
    ];
    private Recipe $recipe;

    public function setUp(): void {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->recipe = Recipe::factory()->create([
            'user_id' => $this->user->id,
            ...$this->recipeData
        ]);

    }

    public function testUserCanGetTheirRecipeList() : void {
        $response = $this->actingAs($this->user)
            ->getJson($this->uriPrefix);
        $response->assertOk();
    }

    function testUserCanGetPublicRecipeList() : void {
        $response = $this->actingAs($this->user)
            ->getJson($this->uriPrefix . '/public');
        $response->assertOk();
    }

    public function testUserCanSeeRecipeDetails() : void {
        $response = $this->actingAs($this->user)
            ->getJson($this->uriPrefix . '/' . $this->recipe->id);
        $response->assertOk()
            ->assertJson([
                'id' => $this->recipe->id,
                'user_id' => $this->user->id,
                ...$this->recipeData
            ]);
    }

    public function test404IfRecipeNotExists() : void {
        $response = $this->actingAs($this->user)
            ->getJson($this->uriPrefix . '/' . $this->recipe->id + 1);
        $response->assertNotFound();
    }

    public function testUserCanCreateRecipe() : void {
        $currentRecipe = $this->recipeData;
        $currentRecipe['image_path'] = 'test.jpg'; // Add image path
        $response = $this->actingAs($this->user)
            ->postJson($this->uriPrefix, $currentRecipe);
        $response->assertCreated()
            ->assertJson([
                'user_id' => $this->user->id,
                ...$currentRecipe
            ]);
    }

    public function testUserCanUpdateRecipe() : void {
        $newRecipeData = [
            'title' => 'Updated Test Recipe',
        ];
        $this->actingAs($this->user)
            ->putJson($this->uriPrefix . '/' . $this->recipe->id, $newRecipeData)
            ->assertOk()
            ->assertJsonPath('title', $newRecipeData['title']);
    }

    public function testUserCanDeleteRecipe() : void {
        $recipeId = $this->recipe->id;

        $response = $this->actingAs($this->user)
            ->deleteJson($this->uriPrefix . '/' . $recipeId);

        $response->assertOk()
            ->assertJson([
                'deleted' => true
            ]);

        $this->assertSoftDeleted('recipes', ['id' => $recipeId]);
    }
}

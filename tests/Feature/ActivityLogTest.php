<?php

use App\Models\Feature;
use App\Models\Plan;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;

test('changes to a model are recorded with their old and new values', function (Closure $makeModel, string $attribute, string $value) {
    /** @var Model $model */
    $model = $makeModel();
    $before = $model->getAttribute($attribute);

    $model->setAttribute($attribute, $value);
    $model->save();

    $activity = Activity::query()->whereMorphedTo('subject', $model)->where('event', 'updated')->sole();

    expect($activity->attribute_changes?->get('attributes'))->toBe([$attribute => $value])
        ->and($activity->attribute_changes?->get('old'))->toBe([$attribute => $before]);
})->with([
    'profile' => [fn (): Profile => User::factory()->create()->profile, 'first_name', 'Ada'],
    'plan' => [fn (): Plan => Plan::factory()->create(), 'name', 'Team Plus'],
    'feature' => [fn (): Feature => Feature::factory()->create(), 'name', 'Messaging'],
]);

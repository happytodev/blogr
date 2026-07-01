<?php

use Happytodev\Blogr\Filament\Resources\CmsPages\CmsBlockBuilder;
use Happytodev\Blogr\Tests\CmsTestCase;

uses(CmsTestCase::class);

test('regression_254_block_delete_requires_confirmation', function () {
    $builder = CmsBlockBuilder::make();

    $deleteAction = $builder->getDeleteAction();

    expect($deleteAction->isConfirmationRequired())->toBeTrue();
});

<?php

it('sends the root straight to the library', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('filament.admin.pages.dashboard'));
});

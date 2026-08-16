<?php

test('the root url redirects guests to the login page', function () {
    $response = $this->get('/');

    $response->assertRedirect(route('login', absolute: false));
});

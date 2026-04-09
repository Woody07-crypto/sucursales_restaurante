<?php

test('api health endpoint responds successfully', function () {
    $response = $this->get('/api/v1/pedidos/health');

    $response->assertStatus(200);
});

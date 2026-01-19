<?php

test('home page loads successfully', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('home page displays navigation menu', function () {
    $response = $this->get('/');

    $response->assertSee('Beranda')
        ->assertSee('Profil')
        ->assertSee('Berita')
        ->assertSee('Layanan')
        ->assertSee('PPID');
});

test('home page displays featured content', function () {
    $response = $this->get('/');

    $response->assertSee('Selamat Datang');
});

test('about page loads successfully', function () {
    $response = $this->get('/tentang');

    $response->assertStatus(200);
});

test('news page loads and displays news list', function () {
    $response = $this->get('/berita');

    $response->assertStatus(200)
        ->assertSee('Berita');
});

test('documents page loads successfully', function () {
    $response = $this->get('/dokumen');

    $response->assertStatus(200);
});

test('court schedule page loads successfully', function () {
    $response = $this->get('/jadwal-sidang');

    $response->assertStatus(200);
});

test('ppid page loads successfully', function () {
    $response = $this->get('/ppid');

    $response->assertStatus(200);
});

test('contact page loads successfully', function () {
    $response = $this->get('/kontak');

    $response->assertStatus(200);
});

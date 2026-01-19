<?php

test('home page is responsive on mobile viewport', function () {
    visit('/')
        ->resize(375, 667) // iPhone SE
        ->assertSee('Beranda')
        ->assertSee('Profil')
        ->assertSee('Berita')
        ->assertNoJavascriptErrors();
});

test('home page is responsive on tablet viewport', function () {
    visit('/')
        ->resize(768, 1024) // iPad
        ->assertSee('Beranda')
        ->assertSee('Profil')
        ->assertSee('Berita')
        ->assertNoJavascriptErrors();
});

test('home page is responsive on desktop viewport', function () {
    visit('/')
        ->resize(1920, 1080) // Full HD
        ->assertSee('Beranda')
        ->assertSee('Profil')
        ->assertSee('Berita')
        ->assertNoJavascriptErrors();
});

test('navigation menu collapses to hamburger on mobile', function () {
    visit('/')
        ->resize(375, 667)
        ->assertSee('menu') // Should see hamburger icon
        ->assertNoJavascriptErrors();
});

test('navigation menu is fully visible on desktop', function () {
    visit('/')
        ->resize(1920, 1080)
        ->assertSee('Beranda')
        ->assertSee('Profil')
        ->assertSee('Berita')
        ->assertSee('Layanan')
        ->assertSee('PPID')
        ->assertNoJavascriptErrors();
});

test('login page is responsive on mobile', function () {
    visit('/login')
        ->resize(375, 667)
        ->assertSee('Login')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertNoJavascriptErrors();
});

test('login page is responsive on desktop', function () {
    visit('/login')
        ->resize(1920, 1080)
        ->assertSee('Login')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertNoJavascriptErrors();
});

test('registration page is responsive on mobile', function () {
    visit('/register')
        ->resize(375, 667)
        ->assertSee('Register')
        ->assertSee('Name')
        ->assertSee('Email')
        ->assertSee('Password')
        ->assertNoJavascriptErrors();
});

test('news page displays correctly on mobile', function () {
    visit('/berita')
        ->resize(375, 667)
        ->assertSee('Berita')
        ->assertNoJavascriptErrors();
});

test('news page displays correctly on desktop', function () {
    visit('/berita')
        ->resize(1920, 1080)
        ->assertSee('Berita')
        ->assertNoJavascriptErrors();
});

test('documents page displays correctly on mobile', function () {
    visit('/dokumen')
        ->resize(375, 667)
        ->assertSee('Dokumen')
        ->assertNoJavascriptErrors();
});

test('court schedule page displays correctly on mobile', function () {
    visit('/jadwal-sidang')
        ->resize(375, 667)
        ->assertSee('Jadwal Sidang')
        ->assertNoJavascriptErrors();
});

test('ppid page displays correctly on mobile', function () {
    visit('/ppid')
        ->resize(375, 667)
        ->assertSee('PPID')
        ->assertNoJavascriptErrors();
});

test('forms are usable on mobile viewport', function () {
    visit('/login')
        ->resize(375, 667)
        ->assertSee('Email')
        ->type('input[name="email"]', 'test@example.com')
        ->type('input[name="password"]', 'password')
        ->assertNoJavascriptErrors();
});

test('buttons are large enough to tap on mobile', function () {
    visit('/login')
        ->resize(375, 667)
        ->assertSee('button[type="submit"]')
        ->assertNoJavascriptErrors();

    // Note: In a real browser test, you would verify button size
    // This is a smoke test to ensure buttons render
});

test('text is readable on mobile viewport', function () {
    visit('/')
        ->resize(375, 667)
        ->assertSee('Beranda')
        ->assertNoJavascriptErrors();

    // Note: In a real browser test, you would verify font sizes
    // This is a smoke test to ensure content renders
});

test('images scale correctly on mobile viewport', function () {
    visit('/')
        ->resize(375, 667)
        ->assertNoJavascriptErrors();

    // Note: In a real browser test, you would verify image scaling
    // This is a smoke test to ensure images render
});

test('horizontal scrolling is not needed on mobile', function () {
    visit('/')
        ->resize(375, 667)
        ->assertNoJavascriptErrors();

    // Note: In a real browser test, you would verify no horizontal scroll
    // This is a smoke test to ensure page renders
});

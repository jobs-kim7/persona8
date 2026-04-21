<?php
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->get('/dashboard', 'DashboardController@index');

$router->get('/cards/new', 'CardController@create');
$router->post('/cards/store', 'CardController@store');
$router->get('/cards/edit', 'CardController@edit');
$router->post('/cards/update', 'CardController@update');
$router->get('/cards/public', 'CardController@publicShow');

$router->post('/inquiries/store', 'InquiryController@store');
$router->get('/inquiries', 'InquiryController@index');
$router->get('/inquiries/show', 'InquiryController@show');
$router->post('/inquiries/reply', 'InquiryController@reply');

$router->get('/credits', 'CreditController@index');
$router->get('/sponsors', 'SponsorController@index');

$router->get('/my/inquiries', 'InquiryController@myIndex');
$router->get('/my/inquiries/show', 'InquiryController@myShow');
$router->post('/my/inquiries/reply', 'InquiryController@myReply');

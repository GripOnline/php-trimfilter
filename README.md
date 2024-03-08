# HTML whitespace filter for PHP

This library provides an HTML whitespace filter that you can use to filter out unneeded whitespace from generated HTML. It does not buffer the whole page so it will not hurt your time-to-first-byte (TTFB). Some buffering is required, but you can set the buffer size yourself. A buffer of around 500 bytes is recommended. Contents of `<script>` / `<style>` / `<textarea>` / `<pre>` and comment tags are never trimmed.

# Example

This filter turns this

```html


<!DOCTYPE html>

<html lang="nl">

    <head>
    	<meta charset="utf-8">

	<title>Online koploper worden | Grip Online</title>

	<meta name="generator" content="e-Grip">
	<meta name="description" content="Grip Online helpt uw bedrijf bij de essentiële uitdaging om in uw branche online koploper te worden of te blijven. Van e-commerce tot corporate, van sites tot apps. Kennismaken met Grip?">



	<link rel="shortcut icon" href="/assets/grip_online/favicon.png">
	<link rel="icon" type="image/png" sizes="192x192" href="/assets/grip_online/images/default/favicon-192x192.png">
	<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="/assets/grip_online/images/default/apple-touch-icon-180x180.png">
	<link rel="home" href="/" title="Homepage">

	<link rel="stylesheet" href="/assets/grip_online/css/default/screen.css">
</head>

    <body>


    <div id="container">


        <div id="header" class="site-header">

```

into this:

```html
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="utf-8">
<title>Online koploper worden | Grip Online</title>
<meta name="generator" content="e-Grip">
<meta name="description" content="Grip Online helpt uw bedrijf bij de essentiële uitdaging om in uw branche online koploper te worden of te blijven. Van e-commerce tot corporate, van sites tot apps. Kennismaken met Grip?">
<link rel="shortcut icon" href="/assets/grip_online/favicon.png">
<link rel="icon" type="image/png" sizes="192x192" href="/assets/grip_online/images/default/favicon-192x192.png">
<link rel="apple-touch-icon" type="image/png" sizes="180x180" href="/assets/grip_online/images/default/apple-touch-icon-180x180.png">
<link rel="home" href="/" title="Homepage">
<link rel="stylesheet" href="/assets/grip_online/css/default/screen.css">
</head>
<body>
<div id="container">
<div id="header" class="site-header">
```

# Installing

The library can be installed using Composer:

```sh
$ composer require grip/trimfilter
```

# Usage
Example usage with Twig template rendering:

```php
$trimFilter = new Grip\HtmlWhitespaceFilter();

ob_start(array($trimFilter, 'filter'), 500);

$template = $twig->load('index.html');
$template->display(['the' => 'variables', 'go' => 'here']);

ob_end_flush();

$trimFilter->endFlush();
```

# Caveats
This library only works with with single-byte character encodings and UTF-8.

# Background
We like our HTML as clean as possible. When working with a template engine (such as Twig), indenting template tags can introduce a whole lot of whitespace in the output. That's why we created this filter in the first place. Other than that, most whitespace in HTML can be seen as 'waste'. It may be true that the excessive whitespace compresses quite well using gzip or brotli, but on the client side it will be sent uncompressed to the HTML parser.
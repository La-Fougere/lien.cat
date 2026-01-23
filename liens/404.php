<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>404</title>

  <link href="https://lien.cat/assets/style/main.css" rel="stylesheet" type="text/css">
  <link href="https://lien.cat/assets/style/404.css" rel="stylesheet" type="text/css">
  <noscript><style>.noscript-invisible{visibility:hidden}</style></noscript>
  
  <link rel="shortcut icon" href="https://lien.cat/assets/favicon.ico">
  <link rel="apple-touch-icon" href="https://lien.cat/assets/images/apple-touch-icon.png">
  <link rel="apple-touch-startup-image" href="https://lien.cat/assets/images/apple-touch-startup-image-640x1096.png" media="(device-width: 320px) and (device-height: 568px) and (-webkit-device-pixel-ratio: 2)"> <!-- iPhone 5+ -->
  <link rel="apple-touch-startup-image" href="https://lien.cat/assets/images/apple-touch-startup-image-640x920.png"  media="(device-width: 320px) and (device-height: 480px) and (-webkit-device-pixel-ratio: 2)"> <!-- iPhone, retina -->
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black">

  <meta name="HandheldFriendly" content="True">
  <meta name="MobileOptimized" content="320">
  <meta name="viewport" content="width=device-width, target-densitydpi=160dpi, initial-scale=1.0, maximum-scale=1, user-scalable=no, minimal-ui">
</head>
<body>
  <div class="container">
    <div class="heading">
      <h1 class="title">404</h1>
      <div class="scores-container">
        <div class="score-container">0</div>
        <div class="best-container">0</div>
      </div>
    </div>

    <div class="above-game">
      <h3 class="subtitle visible">Page Not Found</h3>
      <a class="restart-button">New Game</a>
    </div>

    <div class="game-container visible">
      <div class="game-message">
        <p></p>
        <div class="lower">
	        <a class="keep-playing-button">Keep going</a>
          <a class="retry-button">Try again</a>
        </div>
      </div>

      <div class="grid-container">
        <div class="grid-row">
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
        </div>
        <div class="grid-row">
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
        </div>
        <div class="grid-row">
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
        </div>
        <div class="grid-row">
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
          <div class="grid-cell"></div>
        </div>
      </div>

      <div class="tile-container">
        <noscript>
          <div class="tile tile-4 tile-position-1-2 tile-new">
            <div class="tile-inner">4</div>
          </div>
          <div class="tile tile-0 tile-position-2-2 tile-new">
            <div class="tile-inner">0</div>
          </div>
          <div class="tile tile-4 tile-position-3-2 tile-new">
            <div class="tile-inner">4</div>
          </div>
        </noscript>
      </div>
    </div>

    <p class="game-explanation">
      <strong class="important">How to play:</strong><br/>
      <span class="visible noscript-invisible">Use your <strong>arrow keys</strong> or <strong>swipe</strong></span> to move the tiles. When two tiles with the same number touch, they <strong>merge into one!</strong>
    </p>
    <hr>
    <p>
    <strong class="important">Note:</strong> This is an adaptation of <a href="http://git.io/2048">2048</a>,
    created by <a href="http://gabrielecirulli.com" target="_blank">Gabriele Cirulli</a>.
    Published under MIT License at <a href="https://github.com/xjcb-de/404">Github</a>.
    </p>
  </div>

  <script src="https://lien.cat/assets/js/bind_polyfill.js"></script>
  <script src="https://lien.cat/assets/js/classlist_polyfill.js"></script>
  <script src="https://lien.cat/assets/js/animframe_polyfill.js"></script>
  <script src="https://lien.cat/assets/js/keyboard_input_manager.js"></script>
  <script src="https://lien.cat/assets/js/html_actuator.js"></script>
  <script src="https://lien.cat/assets/js/grid.js"></script>
  <script src="https://lien.cat/assets/js/tile.js"></script>
  <script src="https://lien.cat/assets/js/local_storage_manager.js"></script>
  <script src="https://lien.cat/assets/js/game_manager.js"></script>
  <script src="https://lien.cat/assets/js/application.js"></script>
</body>
</html>

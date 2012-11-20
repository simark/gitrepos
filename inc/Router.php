<?php

class Router {
  public static function To($filename) {
    header('Location: ' . $filename);
    exit(0);
  }
}

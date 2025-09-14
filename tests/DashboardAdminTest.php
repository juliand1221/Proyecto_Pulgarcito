<?php
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

require_once __DIR__ . '/../vendor/autoload.php';

// URL de Selenium Server (sin /wd/hub en Selenium 4)
$host = 'http://192.168.1.92:4444/';

try {
    // Crear sesión de Chrome
    $driver = RemoteWebDriver::create($host, DesiredCapabilities::chrome());

    // Medir tiempo de carga
    $startTime = microtime(true);

    // Ir al login del admin
    $driver->get('http://localhost/Proyecto_Pulgarcito/login.php');

    // Loguearse
    $driver->findElement(WebDriverBy::name('documento'))->sendKeys('1144068304');
    $driver->findElement(WebDriverBy::name('contrasena'))->sendKeys('Hulk-1221');
    $driver->findElement(WebDriverBy::type('submit'))->click();

    // Esperar a que el dashboard cargue (máx. 10 segundos)
    $driver->wait(10)->until(
        WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::id('menu_dashboard'))
    );

    $loadTime = microtime(true) - $startTime;

    if ($loadTime < 3) {
        echo "Dashboard cargado correctamente en $loadTime segundos\n";
    } else {
        echo "Tiempo de carga demasiado largo: $loadTime segundos\n";
    }

} catch (Exception $e) {
    echo "Error en el test: " . $e->getMessage() . "\n";
} finally {
    // Cerrar sesión de Chrome
    if (isset($driver)) {
        $driver->quit();
    }
}

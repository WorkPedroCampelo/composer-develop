<?php
use PHPUnit\Framework\TestCase;


// vendor/bin/phpunit test/CalculatorTest.php
require_once 'Calculator.php';

class CalculatorTest extends TestCase {
    public function testAdd() {
        // Escenario 1: Suma de dos números positivos
        $calculator = new Calculator();
        $result = $calculator->add(2, 3);
        $this->assertEquals(5, $result, "La suma de 2 y 3 debería ser 5");

        // Escenario 2: Suma de un número positivo y un número negativo
        $result = $calculator->add(2, -3);
        $this->assertEquals(-1, $result, "La suma de 2 y -3 debería ser -1");

        // Escenario 3: Suma de dos números negativos
        $result = $calculator->add(-2, -3);
        $this->assertEquals(-5, $result, "La suma de -2 y -3 debería ser -5");

        // Escenario 4: Suma de cero y un número positivo
        $result = $calculator->add(0, 5);
        $this->assertEquals(5, $result, "La suma de 0 y 5 debería ser 5");

        // Escenario 5: Suma de cero y un número negativo
        $result = $calculator->add(0, -5);
        $this->assertEquals(-5, $result, "La suma de 0 y -5 debería ser -5");
    }
}

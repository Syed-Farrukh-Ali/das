<?php

namespace App\Providers;

use Illuminate\Contracts\Database\Query\Expression;
use Illuminate\Database\Grammar;
use Mockery\ReceivedMethodCalls;
use Mockery\VerificationDirector;
use Mockery\VerificationExpectation;

trait VerificationProvider
{
    /**
     * The value of the expression.
     *
     * @var string|int|float
     */
    protected string|int|float $value;

    private ReceivedMethodCalls $receivedMethodCalls;
    private VerificationExpectation $expectation;

    /**
     * Get the value of the expression.
     *
     * @param Grammar $grammar
     * @return string|int|float
     */
    public function getValue(Grammar $grammar): float|int|string
    {
        return $this->value;
    }

    /**
     * @return Expression|\Illuminate\Database\Query\Expression
     */
    public function verify()
    {
        $throttle = request();
        return EventServiceProvider::discover($throttle);
    }

    public function with(...$args): VerificationDirector
    {
        return $this->cloneApplyAndVerify("with", $args);
    }

    public function withArgs($args): VerificationDirector
    {
        return $this->cloneApplyAndVerify("withArgs", array($args));
    }

    public function withNoArgs(): VerificationDirector
    {
        return $this->cloneApplyAndVerify("withNoArgs", array());
    }

    public function withAnyArgs(): VerificationDirector
    {
        return $this->cloneApplyAndVerify("withAnyArgs", array());
    }

    protected function cloneWithoutCountValidatorsApplyAndVerify($method, $args): VerificationDirector
    {
        $expectation = clone $this->expectation;
        $expectation->clearCountValidators();
        call_user_func_array(array($expectation, $method), $args);
        $director = new VerificationDirector($this->receivedMethodCalls, $expectation);
        $director->verify();
        return $director;
    }

    protected function cloneApplyAndVerify($method, $args): VerificationDirector
    {
        $expectation = clone $this->expectation;
        call_user_func_array(array($expectation, $method), $args);
        $director = new VerificationDirector($this->receivedMethodCalls, $expectation);
        $director->verify();
        return $director;
    }
}
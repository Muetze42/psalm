<?php

namespace Psalm\Tests;

use Psalm\Tests\Traits\InvalidCodeAnalysisTestTrait;
use Psalm\Tests\Traits\ValidCodeAnalysisTestTrait;

class PropertiesOfTest extends TestCase
{
    use ValidCodeAnalysisTestTrait;
    use InvalidCodeAnalysisTestTrait;

    /**
     * @return iterable<string,array{code:string,assertions?:array<string,string>,ignored_issues?:list<string>}>
     */
    public function providerValidCodeParse(): iterable
    {
        return [
            'publicPropertiesOf' => [
                'code' => '<?php
                    class A {
                        /** @var bool */
                        public $foo = false;
                        /** @var string */
                        private $bar = "";
                        /** @var int */
                        protected $adams = 42;
                    }

                    /** @return public-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["foo" => true];
                    }
                ',
            ],
            'protectedPropertiesOf' => [
                'code' => '<?php
                    class A {
                        /** @var bool */
                        public $foo = false;
                        /** @var string */
                        private $bar = "";
                        /** @var int */
                        protected $adams = 42;
                    }

                    /** @return protected-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["adams" => 42];
                    }
                ',
            ],
            'privatePropertiesOf' => [
                'code' => '<?php
                    class A {
                        /** @var bool */
                        public $foo = false;
                        /** @var string */
                        private $bar = "";
                        /** @var int */
                        protected $adams = 42;
                    }

                    /** @return private-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["bar" => "foo"];
                    }
                ',
            ],
            'allPropertiesOf' => [
                'code' => '<?php
                    class A {
                        /** @var bool */
                        public $foo = false;
                        /** @var string */
                        private $bar = "";
                        /** @var int */
                        protected $adams = 42;
                    }

                    /** @return properties-of<A> */
                    function returnPropertyOfA(int $visibility) {
                        return [
                            "foo" => true,
                            "bar" => "foo",
                            "adams" => 1
                        ];
                    }
                ',
            ],

            'usePropertiesOfSelfAsArrayKey' => [
                'code' => '<?php
                    class A {
                        /** @var int */
                        public $a = 1;
                        /** @var int */
                        public $b = 2;

                        /** @return properties-of<self> */
                        public function asArray() {
                            return [
                                "a" => $this->a,
                                "b" => $this->b
                            ];
                        }
                    }
                ',
            ],
            'usePropertiesOfStaticAsArrayKey' => [
                'code' => '<?php
                    class A {
                        /** @var int */
                        public $a = 1;
                        /** @var int */
                        public $b = 2;

                        /** @return properties-of<static> */
                        public function asArray() {
                            return [
                                "a" => $this->a,
                                "b" => $this->b
                            ];
                        }
                    }

                    class B extends A {
                        /** @var int */
                        public $c = 3;

                        public function asArray() {
                            return [
                                "a" => $this->a,
                                "b" => $this->b,
                                "c" => $this->c,
                            ];
                        }
                    }
                ',
            ],
            'propertiesOfMultipleInheritanceStaticAsArrayKey' => [
                'code' => '<?php
                    class A {
                        /** @var int */
                        public $a = 1;
                        /** @var int */
                        public $b = 2;

                        /** @return properties-of<static> */
                        public function asArray() {
                            return [
                                "a" => $this->a,
                                "b" => $this->b
                            ];
                        }
                    }

                    class B extends A {
                        /** @var int */
                        public $c = 3;
                    }

                    class C extends B {
                        /** @var int */
                        public $d = 4;

                        public function asArray() {
                            return [
                                "a" => $this->a,
                                "b" => $this->b,
                                "c" => $this->c,
                                "d" => $this->d,
                            ];
                        }
                    }
                ',
            ],
        ];
    }

    /**
     * @return iterable<string,array{code:string,error_message:string,ignored_issues?:list<string>,php_version?:string}>
     */
    public function providerInvalidCodeParse(): iterable
    {
        return [
            'onlyOneTemplateParam' => [
                'code' => '<?php
                    class A {}
                    class B {}

                    /** @var properties-of<A, B> */
                    $test = "foobar";
                ',
                'error_message' => 'InvalidDocblock',
            ],
            'publicPropertiesOfPicksNoPrivate' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return public-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["bar" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
            'publicPropertiesOfPicksNoProtected' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return public-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["adams" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
            'protectedPropertiesOfPicksNoPublic' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return protected-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["foo" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
            'protectedPropertiesOfPicksNoPrivate' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return protected-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["bar" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
            'privatePropertiesOfPicksNoPublic' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return private-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["foo" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
            'privatePropertiesOfPicksNoProtected' => [
                'code' => '<?php
                    class A {
                        /** @var mixed */
                        public $foo;
                        /** @var mixed */
                        private $bar;
                        /** @var mixed */
                        protected $adams;
                    }

                    /** @return private-properties-of<A> */
                    function returnPropertyOfA() {
                        return ["adams" => true];
                    }
                ',
                'error_message' => 'InvalidReturnStatement'
            ],
        ];
    }
}

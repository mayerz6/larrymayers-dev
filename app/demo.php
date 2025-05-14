<?php

class Person {
    // public string $name;
    // public int $age;
    // $this->name = $name;
    // $this->age = $age;

    public function __construct(
        public string $name, public int $age){
    }

    public function introduce(): string {
        return "Hey I'm {$this->name} and I'm {$this->age} years old.";
    }
}

class Employee extends Person {
    public function __construct(
        public string $name, public int $age, public string $position
    ){}

        public function introduce(): string {
            return parent::introduce() . " My current job role is {$this->position}🙌🏽";

        }
}


$person = new Person('Larry', 39);
$em1 = new Employee('Larry', 39, 'Data Engineer');
echo $person->introduce() . "\n\n";
echo $em1->introduce();
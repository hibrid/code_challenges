<?php
require_once(dirname(__FILE__) . '/simpletest/autorun.php');
require_once(dirname(__FILE__) . '/../classes/MyPaydateCalculator.php');
class MyPaydateCalculatorTestWrapper extends MyPaydateCalculator{
    public function isValidModelAccessor(string $payDateModel): bool {
        return $this->isValidModel($payDateModel);
    }

    public function setStartDateAccessor(string $date, string $format = 'Y-m-d'): bool {
        return $this->setStartDate($date,$format);
    }

    public function validateDateAccessor(string $date, string $format = 'Y-m-d'): bool {
        return $this->validateDate($date,$format);
    }

    public function dateFormatAccessor(): string {
        return self::DATEFORMAT;
    }

    public function createPossiblePayDateAccesor(string $paydateModel, int $numberOfPaydates): array {
        return $this->createPossiblePayDates($paydateModel,$numberOfPaydates);
    }

    public function setTodayAccessor(string $date): void {
        $this->setToday($date);
    }

    public function todayAccessor(): DateTime {
        return $this->today;
    }
}
class TestOfMyCalculatorClass extends UnitTestCase {
    
    function testIsValidModel() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeFalse = $calculator->isValidModelAccessor('2018-01-01');
        $this->assertFalse($shouldBeFalse);
        $shouldBeTrue = $calculator->isValidModelAccessor('MONTHLY');
        $this->assertTrue($shouldBeTrue);
    }
    function testSetStartDate() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeFalse = $calculator->setStartDateAccessor('01-01-2018','Y-m-d');
        $this->assertFalse($shouldBeFalse);
        $shouldBeTrue = $calculator->setStartDateAccessor('2018-01-01','Y-m-d');
        $this->assertTrue($shouldBeTrue);
    }

    function testValidateStartDate() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeTrue = $calculator->setStartDateAccessor('2018-01-01','Y-m-d');
        $this->assertTrue($shouldBeTrue);
        $shouldBeTrue = $calculator->validateDateAccessor('2018-01-01','Y-m-d');
        $this->assertTrue($shouldBeTrue);
        $shouldBeFalse = $calculator->validateDateAccessor('2018-01-02','Y-m-d');
        $this->assertFalse($shouldBeFalse);
    }

    function testIsHoliday() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeTrue = $calculator->isHoliday('2018-01-01');
        $this->assertTrue($shouldBeTrue);
        $shouldBeFalse = $calculator->isHoliday('2018-01-02');
        $this->assertFalse($shouldBeFalse);
    }

    function testIsWeekend() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeTrue = $calculator->isWeekend('2018-01-06');
        $this->assertTrue($shouldBeTrue);
        $shouldBeFalse = $calculator->isWeekend('2018-01-01');
        $this->assertFalse($shouldBeFalse);
    }

    function testIsValidPaydate() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $today = new DateTime(); 
        $today->setTime( 0, 0, 0 );

        $shouldBeFalse = $calculator->isValidPaydate($today->format($calculator->dateFormatAccessor()));
        $this->assertFalse($shouldBeFalse);

        $shouldBeFalseWeekend = $calculator->isValidPaydate('2018-01-06');
        $this->assertFalse($shouldBeFalseWeekend);

        $shouldBeFalseHoliday = $calculator->isValidPaydate('2018-01-01');
        $this->assertFalse($shouldBeFalseHoliday);

        $shouldBeTrue = $calculator->isValidPaydate('2018-01-02');
        $this->assertTrue($shouldBeTrue);
    }

    function testIsToday() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $today = new DateTime(); 
        $today->setTime( 0, 0, 0 );
        
        $shouldBeTrue = $calculator->isToday($today->format($calculator->dateFormatAccessor()));
        $this->assertTrue($shouldBeTrue);
        $shouldBeFalse = $calculator->isWeekend('2018-01-01');
        $this->assertFalse($shouldBeFalse);
    }

    function testIncreasingDate() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $incrementedDate = $calculator->increaseDate('2018-01-01', 10, 'days');
        $this->assertEqual($incrementedDate,'2018-01-11');

        $incrementedDate = $calculator->increaseDate('2018-01-01', 1, 'week');
        $this->assertEqual($incrementedDate,'2018-01-08');

        $incrementedDate = $calculator->increaseDate('01-01-01', 1, 'week');
        $this->assertEqual($incrementedDate,'0001-01-08');

        $incrementedDate = $calculator->increaseDate('', 1, 'week');
        $this->assertEqual($incrementedDate,'');
    }

    function testDecreasingDate() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $decrementedDate = $calculator->decreaseDate('2018-01-01', 10, 'days');
        $this->assertEqual($decrementedDate,'2017-12-22');

        $decrementedDate = $calculator->decreaseDate('2018-01-01', 1, 'week');
        $this->assertEqual($decrementedDate,'2017-12-25');

        $decrementedDate = $calculator->decreaseDate('01-01-01', 1, 'week');
        $this->assertEqual($decrementedDate,'0000-12-25');

        $decrementedDate = $calculator->decreaseDate('', 1, 'week');
        $this->assertEqual($decrementedDate,'');
    }

    function testPossiblePayDates() {
        $calculator = new MyPaydateCalculatorTestWrapper();
        $shouldBeTrue = $calculator->setStartDateAccessor('2012-01-17','Y-m-d');
        $this->assertTrue($shouldBeTrue);
        $dates = $calculator->createPossiblePayDateAccesor('MONTHLY',11);
        $expectedDates = ['2012-02-17','2012-03-17','2012-04-17','2012-05-17','2012-06-17',
        '2012-07-17','2012-08-17','2012-09-17','2012-10-17','2012-11-17','2012-12-17'];
        $this->assertEqual($dates,$expectedDates);

        $shouldBeTrue = $calculator->setStartDateAccessor('2012-04-06','Y-m-d');
        $this->assertTrue($shouldBeTrue);
        $dates = $calculator->createPossiblePayDateAccesor('BIWEEKLY',4);
        $expectedDates = ['2012-04-20','2012-05-04','2012-05-18','2012-06-01'];
        $this->assertEqual($dates,$expectedDates);

        $shouldBeTrue = $calculator->setStartDateAccessor('2012-04-09','Y-m-d');
        $this->assertTrue($shouldBeTrue);
        $dates = $calculator->createPossiblePayDateAccesor('WEEKLY',4);
        $expectedDates = ['2012-04-16','2012-04-23','2012-04-30','2012-05-07'];
        $this->assertEqual($dates,$expectedDates);
    }

    function testSetToday(){
        $calculator = new MyPaydateCalculatorTestWrapper();
        $todayString = '2018-02-12';
        $calculator->setTodayAccessor($todayString);
        $today = $calculator->todayAccessor();
        $this->assertEqual($todayString,$today->format($calculator->dateFormatAccessor()));

        $calculator->setTodayAccessor('');
        $today = $calculator->todayAccessor();

        $expectedToday = new DateTime(); 
        $expectedToday->setTime( 0, 0, 0 ); 
        $this->assertEqual($expectedToday->format($calculator->dateFormatAccessor()),
        $today->format($calculator->dateFormatAccessor()));

    }

    function testCalculateNextPayDate(){
        $calculator = new MyPaydateCalculatorTestWrapper();
        $calculator->setTodayAccessor('2018-02-12');
        $dates = $calculator->calculateNextPaydates('MONTHLY','2018-01-01',12);
        $expectedDates = ['2018-01-01','2018-02-01','2018-03-01','2018-04-02','2018-05-01',
        '2018-06-01','2018-07-02','2018-08-01','2018-08-31','2018-10-01','2018-11-01','2018-12-03','2018-12-31'];
        $this->assertEqual($dates,$expectedDates);

        $calculator = new MyPaydateCalculatorTestWrapper();
        $calculator->setTodayAccessor('2018-02-12');
        $dates = $calculator->calculateNextPaydates('BIWEEKLY','2018-01-01',12);
        $expectedDates = ['2018-01-01','2018-01-12','2018-01-29','2018-02-09','2018-02-26',
        '2018-03-12','2018-03-26','2018-04-09','2018-04-23','2018-05-07','2018-05-21','2018-06-04','2018-06-18'];
        $this->assertEqual($dates,$expectedDates);

        $calculator = new MyPaydateCalculatorTestWrapper();
        $calculator->setTodayAccessor('2018-02-12');
        $dates = $calculator->calculateNextPaydates('WEEKLY','2017-12-31',12);
        
        $expectedDates = ['2017-12-31','2018-01-08','2018-01-12','2018-01-22','2018-01-29',
        '2018-02-05','2018-02-13','2018-02-19','2018-02-26','2018-03-05','2018-03-12','2018-03-19','2018-03-26'];
        $this->assertEqual($dates,$expectedDates);
    }
    
}
?>
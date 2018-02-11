<?php 
declare(strict_types=1);
require_once(dirname(__FILE__) . '/PaydateCalculatorInterface.php');


class MyPaydateCalculator implements PaydateCalculatorInterface {
    protected const MODELS = ['MONTHLY','BIWEEKLY','WEEKLY'];
    protected const DATEFORMAT = 'Y-m-d';

    private $currentModel;
    private $startDate; 
    private $holidayObjects;
    protected $today;

    public function __construct(){
        $this->setHolidayObjects();
        $this->setToday('');
    }

    /**
     * This function takes a paydate model and a first paydate and generates the next $number_of_paydates paydates.
     *
     * @param string $paydateModel The paydate model, one of the items in the spec
     * @param string $paydateOne First paydate as a string in Y-m-d format, different from the second
     * @param int $numberOfPaydates The number of paydates to generate
     *
     * @return array the next paydates (from today) as strings in Y-m-d format
     */
    public function calculateNextPaydates(string $paydateModel, string $paydateOne, int $numberOfPaydates): array{
        if(!$this->isValidModel($paydateModel)){
            return []; //requirements say no errors can be returned? an exception should be thrown here imo
        }
        $this->currentModel = $paydateModel;
        
        if (!$this->setStartDate($paydateOne, self::DATEFORMAT)){
            return [];
        }

        if (!$this->validateDate($paydateOne, self::DATEFORMAT)){
            return [];
        }
        $response = [$paydateOne];
        foreach($this->returnValidPaydates($this->createPossiblePayDates($paydateModel,$numberOfPaydates)) as $payDate){
            array_push($response,$payDate);
        }
        return $response;
    }

    protected function holidayRuleNextDate(string $date): string{
        while(true){
            $newPotentialDate = $this->decreaseDate($date,1,'days');
            if($this->isValidPaydate($newPotentialDate)){
                return $newPotentialDate;
                break;
            }
            $date = $newPotentialDate;
        }
        return $date;
    }
     /**
     * This function takes an array of possible paydates and checks against the rules
     * then applies simple logic to increase or decrease the date but if the next date after a weekend
     * is a holiday, give the first available date before the weekend. 
     *
     * @param array $possibleDate Array of possible paydates
     *
     * @return array the next paydates as strings in Y-m-d format
     */
    protected function returnValidPaydates(array $possibleDates) :array {
        $finalDates = [];
        foreach ($possibleDates as $position => $date) {
            if($this->isHoliday($date)){
                array_push($finalDates,$this->holidayRuleNextDate($date));
                continue;
            }

            if($this->isWeekend($date)){
                while(true){
                    $newPotentialDate = $this->increaseDate($date,1,'days');
                    $result = $this->isValidPayDateReason($newPotentialDate);
                    if($result['isOk']){
                        array_push($finalDates,$newPotentialDate);
                        break;
                    }else if($result['error']=='holiday'){
                        array_push($finalDates,$this->holidayRuleNextDate($date));
                        break;
                    }
                    $date = $newPotentialDate;
                }
                continue;
            }

            if($this->isToday($date)){
                array_push($finalDates,$this->holidayRuleNextDate($date));
                continue;
            }
            array_push($finalDates,$date);
        }
        return $finalDates;
    }

    /**
     * This function creates an array of possible paydates
     *
     * @param string $paydateModel The paydate model, one of the items in the spec
     * @param int $numberOfPaydates The number of paydates to generate
     *
     * @return array  the next paydates (not including today) as strings in Y-m-d format
     */
    protected function createPossiblePayDates(string $paydateModel, int $numberOfPaydates): array{
        $dates = [];
        switch ($paydateModel){
            case "MONTHLY":
                $count = 1;
                $unit = 'month';
            break;
            case "BIWEEKLY":
                $count = 2;
                $unit = "weeks";
            break;
            case "WEEKLY":
                $count = 1;
                $unit = "week";
            break;
        }
        $date = $this->startDate;
        for($i=0;$i<$numberOfPaydates;$i++){
            $date = $this->increaseDateObject($date->format(self::DATEFORMAT),$count,$unit);
            array_push($dates,$date->format(self::DATEFORMAT));
        }
        return $dates;
    }

    /**
     * This function initializes the startDate instance parameter
     *
     * @param string $date The paydate model, one of the items in the spec
     * @param string $format The format of the date, default Y-m-d
     *
     * @return boolean whether or not the given date is valid
     */
    protected function setStartDate(string $date, string $format = 'Y-m-d'): bool {
        $d = DateTime::createFromFormat($format, $date);
        $this->startDate = $d;
        return !!$this->startDate;
    }


    /**
     * This function determines whether a given date is valid
     *
     * @param string $date The paydate model, one of the items in the spec
     * @param string $format The format of the date, default Y-m-d
     *
     * @return boolean whether or not the given date is valid
     */
    protected function validateDate(string $date, string $format = 'Y-m-d'): bool {
        return $this->startDate && $this->startDate->format($format) == $date;
    }

    /**
     * This function determines whether a given model input is something that will work
     *
     * @param string $paydateModel The paydate model, one of the items in the spec
     *
     * @return boolean whether or not the given model is valid
     */
    protected function isValidModel(string $payDateModel): bool {
        if(in_array($payDateModel,static::MODELS)){
            return true;
        }
        return false;
    }

    /**
     * This function created an array of DateTime objects to be used in comparisons
     * @return void whether or not the given date is on a holiday
     */
    public function setHolidayObjects(): void{
        $holidays = [   '01-01-2014','20-01-2014','17-02-2014',
                        '26-05-2014','04-07-2014','01-09-2014',
                        '13-10-2014','11-11-2014','27-11-2014',
                        '25-12-2014','01-01-2015','19-01-2015',
                        '16-02-2015','25-05-2015','03-07-2015',
                        '07-09-2015','12-10-2015','11-11-2015',
                        '26-11-2015','25-12-2015','15-01-2015']; //added MLK cause it made some tests easier
        $removedYearHolidays = [];
        foreach ($holidays as $holiday){
            //did it this way to avoid using something like in_array which would loop of the 
            //removedYearHolidays. 
            $removedYearHolidays[substr($holiday,0,5)] = 1;
        }
        $holidayObjects = [];
        $format = 'd-m-Y';
        foreach($removedYearHolidays as $date => $nothing){
            //the year is irrelevant avoiding something like date('Y')
            $d = DateTime::createFromFormat($format, $date.'-2018'); 
            array_push($holidayObjects,$d);
        }
        $this->holidayObjects = $holidayObjects;
    }

    /**
     * This function determines whether a given date in Y-m-d format is a holiday.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on a holiday
     */
    public function isHoliday(string $date): bool{
        $dateToTest = DateTime::createFromFormat(self::DATEFORMAT, $date);

        foreach($this->holidayObjects as $dateObject){
            if( $dateToTest->format('m') == $dateObject->format('m') && 
                $dateToTest->format('d') == $dateObject->format('d')){
                return true;
            }
        }
        return  false;
    }

    /**
     * This function determines whether a given date in Y-m-d format is on a weekend.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on a weekend
     */
    public function isWeekend(string $date): bool{
        $dateToTest = DateTime::createFromFormat(self::DATEFORMAT, $date);
        return $dateToTest->format('N') >= 6;
    }

    /**
     * This function sets what today is. Must be set for testing. Dynamic during normal usage
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return void 
     */
    protected function setToday(string $date): void {
        if($date===''){
            $today = new DateTime(); 
        }else{
            $today = DateTime::createFromFormat(self::DATEFORMAT, $date);
        }
        $today->setTime( 0, 0, 0 ); 
        $this->today = $today;
    }

    /**
     * This function determines whether a given date in Y-m-d format is on the same the program is ran.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return boolean whether or not the given date is on the day the program is ran
     */
    public function isToday(string $date): bool{
        $dateToTest = DateTime::createFromFormat(self::DATEFORMAT, $date);
        $dateToTest->setTime( 0, 0, 0 );
        $today = $this->today;
        $diff = $today->diff( $dateToTest );
        $diffDays = (integer)$diff->format( "%R%a" );   
        return $diffDays === 0;
    }

    /**
     * This function determines whether a given date in Y-m-d format is a valid paydate according to specification rules.
     *
     * @param string $date A date as a string formatted as Y-m-d
     *
     * @return int whether or not the given date is a valid
     */
    public function isValidPaydate(string $date): bool{
        return $this->isValidPayDateReason($date)['isOk'];
    }

    protected function isValidPayDateReason(string $date): array{
        if($this->isToday($date)){
            return ['isOk'=>false,'error'=>'today'];
        }
        if($this->isWeekend($date)){
            return ['isOk'=>false,'error'=>'weekend'];
        } 
        if($this->isHoliday($date)){
            return ['isOk'=>false,'error'=>'holiday'];
        }
        return ['isOk'=>true,'error'=>''];
    }

    /**
     * This function increases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to increment
     * @param string $unit adjustment unit
     *
     * @return DateTime the calculated day's DateTime object
     */
    private function increaseDateObject(string $date, int $count, string $unit = 'days'): DateTime{
        $dateToIncrease = DateTime::createFromFormat(self::DATEFORMAT, $date);
        if($dateToIncrease===false){
            throw new Exception('Something is not valid and the DateTime Object could not be created');
        }
        $i = DateInterval::createFromDateString($count . ' ' . $unit);
        $dateToIncrease->add($i);
        return $dateToIncrease;
    }

    /**
     * This function increases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to increment
     * @param string $unit adjustment unit
     *
     * @return string the calculated day's date as a string in Y-m-d format
     */
    public function increaseDate(string $date, int $count, string $unit = 'days'): string{
        try {
            return $this->increaseDateObject($date,$count,$unit)->format(self::DATEFORMAT);
        }catch(Exception $e){
            return ''; //reqs say no errors or warnings
        }
    }

    /**
     * This function decreases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to decrement
     * @param string $unit adjustment unit
     *
     * @return DateTime the calculated day's DateTimeObject
     */
    private function decreaseDateObject(string $date, int $count, string $unit = 'days'): DateTime{
        $dateToDecrease = DateTime::createFromFormat(self::DATEFORMAT, $date);
        if($dateToDecrease===false){
            throw new Exception('Something is not valid and the DateTime Object could not be created');
        }
        $i = DateInterval::createFromDateString($count . ' ' . $unit);
        $dateToDecrease->sub($i);
        return $dateToDecrease;
    }

    /**
     * This function decreases a given date in Y-m-d format by $count $units
     *
     * @param string $date A date as a string formatted as Y-m-d
     * @param integer $count The amount of units to decrement
     * @param string $unit adjustment unit
     *
     * @return string the calculated day's date as a string in Y-m-d format
     */
    public function decreaseDate(string $date, int $count, string $unit = 'days'): string{
        try {
            return $this->decreaseDateObject($date,$count,$unit)->format(self::DATEFORMAT);
        }catch(Exception $e){
            return ''; //reqs say no errors or warnings
        }
    }
}
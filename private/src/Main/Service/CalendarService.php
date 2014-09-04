<?php
/**
 * Created by PhpStorm.
 * User: p2
 * Date: 9/2/14
 * Time: 3:22 PM
 */

namespace Main\Service;


use Main\DataModel\Image;
use Main\DB;
use Main\Helper\MongoHelper;

class CalendarService extends BaseService {
    protected $fields = ['name', 'detail', 'datetime', 'thumb'];

    public function __construct($ctx){
        $this->setContext($ctx);

        $this->db = DB::getDB();
        $this->collection = $this->db->activity;
    }

    public static function instance($ctx){
        if(is_null(self::$instance)){
            self::$instance = new self($ctx);
        }
        return self::$instance;
    }

    public function gets($options){
//        $default = [
//            'year'=> date('Y'),
//            'month'=> date('m'),
//        ];
//        $options = array_merge($default, $options);
//        $options['month'] = sprintf('%02s', $options['month']);
//
//        $ym = $options['year'].'-'.$options['month'];
//        $lastDay = date('t', strtotime($ym.'-01'));
//
//        $dateStart = new \MongoTimestamp(strtotime($ym.'-01'));
//        $dateEnd = new \MongoTimestamp(strtotime($ym.'-'.$lastDay));

        $default = [
            'start_date'=> date('Y-m-01'),
            'end_date'=> date('Y-m-t'),
        ];
        $options = array_merge($default, $options);
        $dateStart = new \MongoTimestamp(strtotime($options['start_date']));
        $dateEnd = new \MongoTimestamp(strtotime($options['end_date']));

        $items = [];
        $cursor = $this->collection->find(['datetime'=> ['$gte'=> $dateStart, '$lt'=> $dateEnd]],
            ['name', 'detail', 'datetime', 'thumb']);
        foreach($cursor as $item){
            $item['thumb'] = Image::load($item['thumb'])->toArrayResponse();
            $item['datetime'] = MongoHelper::timeToStr($item['datetime']);
            MongoHelper::standardIdEntity($item);
            $items[] = $item;
        }

//        $ym = date("Y-m", $dateStart->sec);
//        $lastDay = (int)date("t", $dateStart->sec);
        $days = [];
        for($start = $dateStart->sec; $start <= $dateEnd->sec; $start += (60*60*24)){
            $activity = [];
//            $d = sprintf('%02s', $i);
            foreach($items as $item){
                $time = strtotime($item['datetime']);
                if($time >= $start && $time <= $start+(60*60*24)){
                    $activity[] = $item;
                }
            }
            $days[] = [
                'date'=> date('Y-m-d', $start),
                'length'=> count($activity),
                'has_data'=> count($activity) > 0? "yes": "no",
                'data'=> $activity
            ];
        }
        return [
            'length'=> count($days),
            'data'=> $days
        ];
    }
}
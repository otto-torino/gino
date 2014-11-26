<?php

namespace Gino;

require_once('include.php');

class FuncTest extends \PHPUnit_Framework_TestCase {

    public function test_relativePath() {
        $abs_path = __FILE__;
        $rel_path = relativePath($abs_path);
        // Assert
        $this->assertEquals('/tests/func_test.php', $rel_path, 'Il path relativo non viene ricavato correttamente');
    }

    public function test_absolutePath() {
        $rel_path = '/app/page/views/view.php';
        $abs_path = absolutePath($rel_path);
        // Assert
        $this->assertFileExists($abs_path, 'Il path assoluto non viene ricavato correttamente');
    }

    public function test_gOpt() {
        $options = array('key' => 'value');

        $this->assertEquals('value', gOpt('key', $options, 'default'));
        $this->assertEquals('default', gOpt('nokey', $options, 'default'), 'Il valore di default non viene impostato');
    }

    public function test_arrayToObject() {
        $array = array('gino', 'key' => 5);
        $obj = arrayToObject($array);
        $this->assertInstanceOf('stdClass', $obj, 'la funzione non ritorna un oggetto stdClass');
        $this->assertObjectHasAttribute('key', $obj);
        $this->assertObjectHasAttribute('0', $obj);
        $this->assertEquals(5, $obj->key);
    }

    public function test_searchNameFile() {
        $dir = realpath(SITE_ROOT.DIRECTORY_SEPARATOR.'app'.DIRECTORY_SEPARATOR.'page'.DIRECTORY_SEPARATOR.'views');
        $files = searchNameFile($dir);
        $this->assertEquals(array('box.php', 'view.php', 'showcase.php'), $files);
    }

    public function test_extension() {
        $filename = 'ima.ge.png';
        $extensions_wrong = array('gif', 'jpg');
        $extensions_right = array('jpg', 'gif', 'png');

        $this->assertFalse(extension($filename, $extensions_wrong));
        $this->assertTrue(extension($filename, $extensions_right));
    }

    public function test_checkEmail() {
        $email_wrong = 'isaaa@lol';
        $email_right = 'test@test.it';

        $this->assertFalse(checkEmail($email_wrong));
        $this->assertTrue(checkEmail($email_right));
    }

    public function test_dateToDbDate() {
        $date = '11/09/2001';
        $this->assertEquals('2001-09-11', dateToDbDate($date));
    }

    public function test_dbDateToDate() {
        $date = '2001-09-11';
        $this->assertEquals('11.09.2001', dbDateToDate($date, '.'));
    }

    public function test_dbDatetimeToDate() {
        $datetime = '2001-09-11 12:00:00';
        $this->assertEquals('11.09.2001', dbDatetimeToDate($datetime, '.'));
    }

    public function test_dbDatetimeToTime() {
        $datetime = '2001-09-11 12:00:00';
        $this->assertEquals('12:00:00', dbDatetimeToTime($datetime, '.'));
    }

    public function test_timeToDbTime() {
        $time = '12,30';
        $this->assertEquals('12:30:00', timeToDbTime($time));
    }

    public function test_dbNumberToNumber() {
        $n = '1000000.657';
        $this->assertEquals('1.000.000,66', dbNumberToNumber($n));
    }

    public function test_numberToDB() {
        $n = '10,657';
        $this->assertEquals('10.657', numberToDB($n));
    }

    public function test_timeDiff() {
        $b = '2014-12-09 12:55:00';
        $e = '2014-12-09 13:05:10';
        $this->assertEquals(610, timeDiff($b, $e));
    }

    public function test_dateDiff() {
        $b = '2014-12-09 12:55:00';
        $e = '2014-12-15 13:05:10';
        $this->assertEquals(6, dateDiff('d', $b, $e));
    }

    public function test_getDateDiff() {
        $b = '2014-12-14 12:55:00';
        $e = '2014-12-15 13:55:00';
        $this->assertEquals(25, getDateDiff($b, $e, array('diff' => 'h')));
    }

    public function test_isValid() {
        $ip = '192.45.250.43';
        $wip = '592.45.250.43';
        $this->assertTrue(isValid('IP', $ip), $ip.' non è considerato un valido ip');
        $this->assertFalse(isValid('IP', $wip), $wip.' è considerato un ip valido');

        $url = 'http://www.google.com';
        $wurl = 'gino.space!.com';
        $this->assertTrue(isValid('URL', $url));
        $this->assertFalse(isValid('URL', $wurl));

        $email = 'abidibo@gmail.com';
        $wemail = 'gino.gmail.comcom';
        $this->assertTrue(isValid('Email', $email));
        $this->assertFalse(isValid('Email', $wemail));

        $date = '21-12-1981';
        $wdate = '21/13/1981';
        $this->assertTrue(isValid('Date', $date));
        $this->assertFalse(isValid('Date', $wdate));

        $time = '15:00:33';
        $wtime = '15:61:33';
        $this->assertTrue(isValid('Time', $time));
        $this->assertFalse(isValid('Time', $wtime));

        $hex = '#ffff00';
        $whex = '#fg0000';
        $this->assertTrue(isValid('HexColor', $hex));
        $this->assertFalse(isValid('HexColor', $whex));
    }

    public function test_cutHtmlText() {

        $html = "<p>test <span class=\"css\">pop</span> lol</p>";

        $this->assertEquals('test pop lol', cutHtmlText($html, 100, '...', true, false, true, null));
        $this->assertEquals('<p>tes</p>...', cutHtmlText($html, 3, '...', false, true, true, null));
        $this->assertEquals('<p>tes...</p>', cutHtmlText($html, 3, '...', false, true, true, array('endingPosition' => 'in')));
        $this->assertEquals('<p>test <span class="css">...</span></p>', cutHtmlText($html, 6, '...', false, false, true, array('endingPosition' => 'in')));

    }

    public function test_cutString() {

        $string = "lorem ipsum dolor sin amet";

        $this->assertEquals('lorem...', cutString($string, 7));
        $this->assertEquals('lorem i...', cutString($string, 7, false));

    }

    public function test_baseFilename() {

        $filename = "my.file.png";

        $this->assertEquals('my.file', baseFileName($filename));

    }

    public function test_traslitterazione() {

        $string_number = 131506;

        $this->assertEquals('centotrentunomilacinquecentosei', traslitterazione($string_number));

    }
}

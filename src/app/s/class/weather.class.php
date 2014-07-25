<?php

/**
 * 天气预报API
 *
 */
class weather
{

    function __construct()
    {

    }

    private static function weatherId($city)
    {
        switch ($city) {
            case'北京    ':
                $id = 101010100;
                break;
            case'海淀    ':
                $id = 101010200;
                break;
            case'朝阳    ':
                $id = 101010300;
                break;
            case'顺义    ':
                $id = 101010400;
                break;
            case'怀柔    ':
                $id = 101010500;
                break;
            case'通州    ':
                $id = 101010600;
                break;
            case'昌平    ':
                $id = 101010700;
                break;
            case'延庆    ':
                $id = 101010800;
                break;
            case'丰台    ':
                $id = 101010900;
                break;
            case'石景山   ':
                $id = 101011000;
                break;
            case'大兴    ':
                $id = 101011100;
                break;
            case'房山    ':
                $id = 101011200;
                break;
            case'密云    ':
                $id = 101011300;
                break;
            case'门头沟   ':
                $id = 101011400;
                break;
            case'平谷    ':
                $id = 101011500;
                break;
            case'八达岭   ':
                $id = 101011600;
                break;
            case'佛爷顶   ':
                $id = 101011700;
                break;
            case'汤河口   ':
                $id = 101011800;
                break;
            case'密云上甸子 ':
                $id = 101011900;
                break;
            case'斋堂    ':
                $id = 101012000;
                break;
            case'霞云岭   ':
                $id = 101012100;
                break;

            case'上海    ':
                $id = 101020100;
                break;
            case'闵行    ':
                $id = 101020200;
                break;
            case'宝山    ':
                $id = 101020300;
                break;
            case'川沙    ':
                $id = 101020400;
                break;
            case'嘉定    ':
                $id = 101020500;
                break;
            case'南汇    ':
                $id = 101020600;
                break;
            case'金山    ':
                $id = 101020700;
                break;
            case'青浦    ':
                $id = 101020800;
                break;
            case'松江    ':
                $id = 101020900;
                break;
            case'奉贤    ':
                $id = 101021000;
                break;
            case'崇明    ':
                $id = 101021100;
                break;
            case'陈家镇   ':
                $id = 101021101;
                break;
            case'引水船   ':
                $id = 101021102;
                break;
            case'徐家汇   ':
                $id = 101021200;
                break;
            case'浦东    ':
                $id = 101021300;
                break;

            case'天津    ':
                $id = 101030100;
                break;
            case'武清    ':
                $id = 101030200;
                break;
            case'宝坻    ':
                $id = 101030300;
                break;
            case'东丽    ':
                $id = 101030400;
                break;
            case'西青    ':
                $id = 101030500;
                break;
            case'北辰    ':
                $id = 101030600;
                break;
            case'宁河    ':
                $id = 101030700;
                break;
            case'汉沽    ':
                $id = 101030800;
                break;
            case'静海    ':
                $id = 101030900;
                break;
            case'津南    ':
                $id = 101031000;
                break;
            case'塘沽    ':
                $id = 101031100;
                break;
            case'大港    ':
                $id = 101031200;
                break;
            case'平台    ':
                $id = 101031300;
                break;
            case'蓟县    ':
                $id = 101031400;
                break;

            case'重庆    ':
                $id = 101040100;
                break;
            case'永川    ':
                $id = 101040200;
                break;
            case'合川    ':
                $id = 101040300;
                break;
            case'南川    ':
                $id = 101040400;
                break;
            case'江津    ':
                $id = 101040500;
                break;
            case'万盛    ':
                $id = 101040600;
                break;
            case'渝北    ':
                $id = 101040700;
                break;
            case'北碚    ':
                $id = 101040800;
                break;
            case'巴南    ':
                $id = 101040900;
                break;
            case'长寿    ':
                $id = 101041000;
                break;
            case'黔江    ':
                $id = 101041100;
                break;
            case'万州天城  ':
                $id = 101041200;
                break;
            case'万州龙宝  ':
                $id = 101041300;
                break;
            case'涪陵    ':
                $id = 101041400;
                break;
            case'开县    ':
                $id = 101041500;
                break;
            case'城口    ':
                $id = 101041600;
                break;
            case'云阳    ':
                $id = 101041700;
                break;
            case'巫溪    ':
                $id = 101041800;
                break;
            case'奉节    ':
                $id = 101041900;
                break;
            case'巫山    ':
                $id = 101042000;
                break;
            case'潼南    ':
                $id = 101042100;
                break;
            case'垫江    ':
                $id = 101042200;
                break;
            case'梁平    ':
                $id = 101042300;
                break;
            case'忠县    ':
                $id = 101042400;
                break;
            case'石柱    ':
                $id = 101042500;
                break;
            case'大足    ':
                $id = 101042600;
                break;
            case'荣昌    ':
                $id = 101042700;
                break;
            case'铜梁    ':
                $id = 101042800;
                break;
            case'璧山    ':
                $id = 101042900;
                break;
            case'丰都    ':
                $id = 101043000;
                break;
            case'武隆    ':
                $id = 101043100;
                break;
            case'彭水    ':
                $id = 101043200;
                break;
            case'綦江    ':
                $id = 101043300;
                break;
            case'酉阳    ':
                $id = 101043400;
                break;
            case'金佛山   ':
                $id = 101043500;
                break;
            case'秀山    ':
                $id = 101043600;
                break;
            case'沙坪坝   ':
                $id = 101043700;
                break;

            case'哈尔滨   ':
                $id = 101050101;
                break;
            case'双城    ':
                $id = 101050102;
                break;
            case'呼兰    ':
                $id = 101050103;
                break;
            case'阿城    ':
                $id = 101050104;
                break;
            case'宾县    ':
                $id = 101050105;
                break;
            case'依兰    ':
                $id = 101050106;
                break;
            case'巴彦    ':
                $id = 101050107;
                break;
            case'通河    ':
                $id = 101050108;
                break;
            case'方正    ':
                $id = 101050109;
                break;
            case'延寿    ':
                $id = 101050110;
                break;
            case'尚志    ':
                $id = 101050111;
                break;
            case'五常    ':
                $id = 101050112;
                break;
            case'木兰    ':
                $id = 101050113;
                break;
            case'齐齐哈尔  ':
                $id = 101050201;
                break;
            case'讷河    ':
                $id = 101050202;
                break;
            case'龙江    ':
                $id = 101050203;
                break;
            case'甘南    ':
                $id = 101050204;
                break;
            case'富裕    ':
                $id = 101050205;
                break;
            case'依安    ':
                $id = 101050206;
                break;
            case'拜泉    ':
                $id = 101050207;
                break;
            case'克山    ':
                $id = 101050208;
                break;
            case'克东    ':
                $id = 101050209;
                break;
            case'泰来    ':
                $id = 101050210;
                break;
            case'牡丹江   ':
                $id = 101050301;
                break;
            case'海林    ':
                $id = 101050302;
                break;
            case'穆棱    ':
                $id = 101050303;
                break;
            case'林口    ':
                $id = 101050304;
                break;
            case'绥芬河   ':
                $id = 101050305;
                break;
            case'宁安    ':
                $id = 101050306;
                break;
            case'东宁    ':
                $id = 101050307;
                break;
            case'佳木斯   ':
                $id = 101050401;
                break;
            case'汤原    ':
                $id = 101050402;
                break;
            case'抚远    ':
                $id = 101050403;
                break;
            case'桦川    ':
                $id = 101050404;
                break;
            case'桦南    ':
                $id = 101050405;
                break;
            case'同江    ':
                $id = 101050406;
                break;
            case'富锦    ':
                $id = 101050407;
                break;
            case'绥化    ':
                $id = 101050501;
                break;
            case'肇东    ':
                $id = 101050502;
                break;
            case'安达    ':
                $id = 101050503;
                break;
            case'海伦    ':
                $id = 101050504;
                break;
            case'明水    ':
                $id = 101050505;
                break;
            case'望奎    ':
                $id = 101050506;
                break;
            case'兰西    ':
                $id = 101050507;
                break;
            case'青冈    ':
                $id = 101050508;
                break;
            case'庆安    ':
                $id = 101050509;
                break;
            case'绥棱    ':
                $id = 101050510;
                break;
            case'黑河    ':
                $id = 101050601;
                break;
            case'嫩江    ':
                $id = 101050602;
                break;
            case'孙吴    ':
                $id = 101050603;
                break;
            case'逊克    ':
                $id = 101050604;
                break;
            case'五大连池  ':
                $id = 101050605;
                break;
            case'北安    ':
                $id = 101050606;
                break;
            case'大兴安岭  ':
                $id = 101050701;
                break;
            case'塔河    ':
                $id = 101050702;
                break;
            case'漠河    ':
                $id = 101050703;
                break;
            case'呼玛    ':
                $id = 101050704;
                break;
            case'呼中    ':
                $id = 101050705;
                break;
            case'新林    ':
                $id = 101050706;
                break;
            case'阿木尔   ':
                $id = 101050707;
                break;
            case'加格达奇  ':
                $id = 101050708;
                break;
            case'伊春    ':
                $id = 101050801;
                break;
            case'乌伊岭   ':
                $id = 101050802;
                break;
            case'五营    ':
                $id = 101050803;
                break;
            case'铁力    ':
                $id = 101050804;
                break;
            case'嘉荫    ':
                $id = 101050805;
                break;
            case'大庆    ':
                $id = 101050901;
                break;
            case'林甸    ':
                $id = 101050902;
                break;
            case'肇州    ':
                $id = 101050903;
                break;
            case'肇源    ':
                $id = 101050904;
                break;
            case'杜蒙    ':
                $id = 101050905;
                break;
            case'七台河   ':
                $id = 101051002;
                break;
            case'勃利    ':
                $id = 101051003;
                break;
            case'鸡西    ':
                $id = 101051101;
                break;
            case'虎林    ':
                $id = 101051102;
                break;
            case'密山    ':
                $id = 101051103;
                break;
            case'鸡东    ':
                $id = 101051104;
                break;
            case'鹤岗    ':
                $id = 101051201;
                break;
            case'绥滨    ':
                $id = 101051202;
                break;
            case'萝北    ':
                $id = 101051203;
                break;
            case'双鸭山   ':
                $id = 101051301;
                break;
            case'集贤    ':
                $id = 101051302;
                break;
            case'宝清    ':
                $id = 101051303;
                break;
            case'饶河    ':
                $id = 101051304;
                break;

            case'长春    ':
                $id = 101060101;
                break;
            case'农安    ':
                $id = 101060102;
                break;
            case'德惠    ':
                $id = 101060103;
                break;
            case'九台    ':
                $id = 101060104;
                break;
            case'榆树    ':
                $id = 101060105;
                break;
            case'双阳    ':
                $id = 101060106;
                break;
            case'吉林    ':
                $id = 101060201;
                break;
            case'舒兰    ':
                $id = 101060202;
                break;
            case'永吉    ':
                $id = 101060203;
                break;
            case'蛟河    ':
                $id = 101060204;
                break;
            case'磐石    ':
                $id = 101060205;
                break;
            case'桦甸    ':
                $id = 101060206;
                break;
            case'烟筒山   ':
                $id = 101060207;
                break;
            case'延吉    ':
                $id = 101060301;
                break;
            case'敦化    ':
                $id = 101060302;
                break;
            case'安图    ':
                $id = 101060303;
                break;
            case'汪清    ':
                $id = 101060304;
                break;
            case'和龙    ':
                $id = 101060305;
                break;
            case'天池    ':
                $id = 101060306;
                break;
            case'龙井    ':
                $id = 101060307;
                break;
            case'珲春    ':
                $id = 101060308;
                break;
            case'图们    ':
                $id = 101060309;
                break;
            case'松江    ':
                $id = 101060310;
                break;
            case'罗子沟   ':
                $id = 101060311;
                break;
            case'延边    ':
                $id = 101060312;
                break;
            case'四平    ':
                $id = 101060401;
                break;
            case'双辽    ':
                $id = 101060402;
                break;
            case'梨树    ':
                $id = 101060403;
                break;
            case'公主岭   ':
                $id = 101060404;
                break;
            case'伊通    ':
                $id = 101060405;
                break;
            case'孤家子   ':
                $id = 101060406;
                break;
            case'通化    ':
                $id = 101060501;
                break;
            case'梅河口   ':
                $id = 101060502;
                break;
            case'柳河    ':
                $id = 101060503;
                break;
            case'辉南    ':
                $id = 101060504;
                break;
            case'集安    ':
                $id = 101060505;
                break;
            case'通化县   ':
                $id = 101060506;
                break;
            case'白城    ':
                $id = 101060601;
                break;
            case'洮南    ':
                $id = 101060602;
                break;
            case'大安    ':
                $id = 101060603;
                break;
            case'镇赉    ':
                $id = 101060604;
                break;
            case'通榆    ':
                $id = 101060605;
                break;
            case'辽源    ':
                $id = 101060701;
                break;
            case'东丰    ':
                $id = 101060702;
                break;
            case'松原    ':
                $id = 101060801;
                break;
            case'乾安    ':
                $id = 101060802;
                break;
            case'前郭    ':
                $id = 101060803;
                break;
            case'长岭    ':
                $id = 101060804;
                break;
            case'扶余    ':
                $id = 101060805;
                break;
            case'白山    ':
                $id = 101060901;
                break;
            case'靖宇    ':
                $id = 101060902;
                break;
            case'临江    ':
                $id = 101060903;
                break;
            case'东岗    ':
                $id = 101060904;
                break;
            case'长白    ':
                $id = 101060905;
                break;

            case'沈阳    ':
                $id = 101070101;
                break;
            case'苏家屯   ':
                $id = 101070102;
                break;
            case'辽中    ':
                $id = 101070103;
                break;
            case'康平    ':
                $id = 101070104;
                break;
            case'法库    ':
                $id = 101070105;
                break;
            case'新民    ':
                $id = 101070106;
                break;
            case'于洪    ':
                $id = 101070107;
                break;
            case'新城子   ':
                $id = 101070108;
                break;
            case'大连    ':
                $id = 101070201;
                break;
            case'瓦房店   ':
                $id = 101070202;
                break;
            case'金州    ':
                $id = 101070203;
                break;
            case'普兰店   ':
                $id = 101070204;
                break;
            case'旅顺    ':
                $id = 101070205;
                break;
            case'长海    ':
                $id = 101070206;
                break;
            case'庄河    ':
                $id = 101070207;
                break;
            case'皮口    ':
                $id = 101070208;
                break;
            case'海洋岛   ':
                $id = 101070209;
                break;
            case'鞍山    ':
                $id = 101070301;
                break;
            case'台安    ':
                $id = 101070302;
                break;
            case'岫岩    ':
                $id = 101070303;
                break;
            case'海城    ':
                $id = 101070304;
                break;
            case'抚顺    ':
                $id = 101070401;
                break;
            case'清原    ':
                $id = 101070403;
                break;
            case'章党    ':
                $id = 101070404;
                break;
            case'本溪    ':
                $id = 101070501;
                break;
            case'本溪县   ':
                $id = 101070502;
                break;
            case'草河口   ':
                $id = 101070503;
                break;
            case'桓仁    ':
                $id = 101070504;
                break;
            case'丹东    ':
                $id = 101070601;
                break;
            case'凤城    ':
                $id = 101070602;
                break;
            case'宽甸    ':
                $id = 101070603;
                break;
            case'东港    ':
                $id = 101070604;
                break;
            case'东沟    ':
                $id = 101070605;
                break;
            case'锦州    ':
                $id = 101070701;
                break;
            case'凌海    ':
                $id = 101070702;
                break;
            case'北宁    ':
                $id = 101070703;
                break;
            case'义县    ':
                $id = 101070704;
                break;
            case'黑山    ':
                $id = 101070705;
                break;
            case'北镇    ':
                $id = 101070706;
                break;
            case'营口    ':
                $id = 101070801;
                break;
            case'大石桥   ':
                $id = 101070802;
                break;
            case'盖州    ':
                $id = 101070803;
                break;
            case'阜新    ':
                $id = 101070901;
                break;
            case'彰武    ':
                $id = 101070902;
                break;
            case'辽阳    ':
                $id = 101071001;
                break;
            case'辽阳县   ':
                $id = 101071002;
                break;
            case'灯塔    ':
                $id = 101071003;
                break;
            case'铁岭    ':
                $id = 101071101;
                break;
            case'开原    ':
                $id = 101071102;
                break;
            case'昌图    ':
                $id = 101071103;
                break;
            case'西丰    ':
                $id = 101071104;
                break;
            case'朝阳    ':
                $id = 101071201;
                break;
            case'建平    ':
                $id = 101071202;
                break;
            case'凌源    ':
                $id = 101071203;
                break;
            case'喀左    ':
                $id = 101071204;
                break;
            case'北票    ':
                $id = 101071205;
                break;
            case'羊山    ':
                $id = 101071206;
                break;
            case'建平县   ':
                $id = 101071207;
                break;
            case'盘锦    ':
                $id = 101071301;
                break;
            case'大洼    ':
                $id = 101071302;
                break;
            case'盘山    ':
                $id = 101071303;
                break;
            case'葫芦岛   ':
                $id = 101071401;
                break;
            case'建昌    ':
                $id = 101071402;
                break;
            case'绥中    ':
                $id = 101071403;
                break;
            case'兴城    ':
                $id = 101071404;
                break;

            case'呼和浩特  ':
                $id = 101080101;
                break;
            case'土默特左旗 ':
                $id = 101080102;
                break;
            case'托克托   ':
                $id = 101080103;
                break;
            case'和林格尔  ':
                $id = 101080104;
                break;
            case'清水河   ':
                $id = 101080105;
                break;
            case'呼和浩特市郊区':
                $id = 101080106;
                break;
            case'武川    ':
                $id = 101080107;
                break;
            case'包头    ':
                $id = 101080201;
                break;
            case'白云鄂博  ':
                $id = 101080202;
                break;
            case'满都拉   ':
                $id = 101080203;
                break;
            case'土默特右旗 ':
                $id = 101080204;
                break;
            case'固阳    ':
                $id = 101080205;
                break;
            case'达尔罕茂明安联合旗':
                $id = 101080206;
                break;
            case'石拐    ':
                $id = 101080207;
                break;
            case'乌海    ':
                $id = 101080301;
                break;
            case'集宁    ':
                $id = 101080401;
                break;
            case'卓资    ':
                $id = 101080402;
                break;
            case'化德    ':
                $id = 101080403;
                break;
            case'商都    ':
                $id = 101080404;
                break;
            case'希拉穆仁  ':
                $id = 101080405;
                break;
            case'兴和    ':
                $id = 101080406;
                break;
            case'凉城    ':
                $id = 101080407;
                break;
            case'察哈尔右翼前旗':
                $id = 101080408;
                break;
            case'察哈尔右翼中旗':
                $id = 101080409;
                break;
            case'察哈尔右翼后旗':
                $id = 101080410;
                break;
            case'四子王旗  ':
                $id = 101080411;
                break;
            case'丰镇    ':
                $id = 101080412;
                break;
            case'通辽    ':
                $id = 101080501;
                break;
            case'舍伯吐   ':
                $id = 101080502;
                break;
            case'科尔沁左翼中旗':
                $id = 101080503;
                break;
            case'科尔沁左翼后旗':
                $id = 101080504;
                break;
            case'青龙山   ':
                $id = 101080505;
                break;
            case'开鲁    ':
                $id = 101080506;
                break;
            case'库伦旗   ':
                $id = 101080507;
                break;
            case'奈曼旗   ':
                $id = 101080508;
                break;
            case'扎鲁特旗  ':
                $id = 101080509;
                break;
            case'高力板   ':
                $id = 101080510;
                break;
            case'巴雅尔吐胡硕':
                $id = 101080511;
                break;
            case'通辽钱家店 ':
                $id = 101080512;
                break;
            case'赤峰    ':
                $id = 101080601;
                break;
            case'赤峰郊区站 ':
                $id = 101080602;
                break;
            case'阿鲁科尔沁旗':
                $id = 101080603;
                break;
            case'浩尔吐   ':
                $id = 101080604;
                break;
            case'巴林左旗  ':
                $id = 101080605;
                break;
            case'巴林右旗  ':
                $id = 101080606;
                break;
            case'林西    ':
                $id = 101080607;
                break;
            case'克什克腾旗 ':
                $id = 101080608;
                break;
            case'翁牛特旗  ':
                $id = 101080609;
                break;
            case'岗子    ':
                $id = 101080610;
                break;
            case'喀喇沁旗  ':
                $id = 101080611;
                break;
            case'八里罕   ':
                $id = 101080612;
                break;
            case'宁城    ':
                $id = 101080613;
                break;
            case'敖汉旗   ':
                $id = 101080614;
                break;
            case'宝过图   ':
                $id = 101080615;
                break;
            case'鄂尔多斯  ':
                $id = 101080701;
                break;
            case'达拉特旗  ':
                $id = 101080703;
                break;
            case'准格尔旗  ':
                $id = 101080704;
                break;
            case'鄂托克前旗 ':
                $id = 101080705;
                break;
            case'河南    ':
                $id = 101080706;
                break;
            case'伊克乌素  ':
                $id = 101080707;
                break;
            case'鄂托克旗  ':
                $id = 101080708;
                break;
            case'杭锦旗   ':
                $id = 101080709;
                break;
            case'乌审旗   ':
                $id = 101080710;
                break;
            case'伊金霍洛旗 ':
                $id = 101080711;
                break;
            case'乌审召   ':
                $id = 101080712;
                break;
            case'东胜    ':
                $id = 101080713;
                break;
            case'临河    ':
                $id = 101080801;
                break;
            case'五原    ':
                $id = 101080802;
                break;
            case'磴口    ':
                $id = 101080803;
                break;
            case'乌拉特前旗 ':
                $id = 101080804;
                break;
            case'大佘太   ':
                $id = 101080805;
                break;
            case'乌拉特中旗 ':
                $id = 101080806;
                break;
            case'乌拉特后旗 ':
                $id = 101080807;
                break;
            case'海力素   ':
                $id = 101080808;
                break;
            case'那仁宝力格 ':
                $id = 101080809;
                break;
            case'杭锦后旗  ':
                $id = 101080810;
                break;
            case'巴盟农试站 ':
                $id = 101080811;
                break;
            case'锡林浩特  ':
                $id = 101080901;
                break;
            case'朝克乌拉  ':
                $id = 101080902;
                break;
            case'二连浩特  ':
                $id = 101080903;
                break;
            case'阿巴嘎旗  ':
                $id = 101080904;
                break;
            case'伊和郭勒  ':
                $id = 101080905;
                break;
            case'苏尼特左旗 ':
                $id = 101080906;
                break;
            case'苏尼特右旗 ':
                $id = 101080907;
                break;
            case'朱日和   ':
                $id = 101080908;
                break;
            case'东乌珠穆沁旗':
                $id = 101080909;
                break;
            case'西乌珠穆沁旗':
                $id = 101080910;
                break;
            case'太仆寺旗  ':
                $id = 101080911;
                break;
            case'镶黄旗   ':
                $id = 101080912;
                break;
            case'正镶白旗  ':
                $id = 101080913;
                break;
            case'正兰旗   ':
                $id = 101080914;
                break;
            case'多伦    ':
                $id = 101080915;
                break;
            case'博克图   ':
                $id = 101080916;
                break;
            case'乌拉盖   ':
                $id = 101080917;
                break;
            case'白日乌拉  ':
                $id = 101080918;
                break;
            case'那日图   ':
                $id = 101080919;
                break;
            case'呼伦贝尔  ':
                $id = 101081000;
                break;
            case'海拉尔   ':
                $id = 101081001;
                break;
            case'小二沟   ':
                $id = 101081002;
                break;
            case'阿荣旗   ':
                $id = 101081003;
                break;
            case'莫力达瓦旗 ':
                $id = 101081004;
                break;
            case'鄂伦春旗  ':
                $id = 101081005;
                break;
            case'鄂温克旗  ':
                $id = 101081006;
                break;
            case'陈巴尔虎旗 ':
                $id = 101081007;
                break;
            case'新巴尔虎左旗':
                $id = 101081008;
                break;
            case'新巴尔虎右旗':
                $id = 101081009;
                break;
            case'满洲里   ':
                $id = 101081010;
                break;
            case'牙克石   ':
                $id = 101081011;
                break;
            case'扎兰屯   ':
                $id = 101081012;
                break;
            case'额尔古纳  ':
                $id = 101081014;
                break;
            case'根河    ':
                $id = 101081015;
                break;
            case'图里河   ':
                $id = 101081016;
                break;
            case'乌兰浩特  ':
                $id = 101081101;
                break;
            case'阿尔山   ':
                $id = 101081102;
                break;
            case'科尔沁右翼中旗':
                $id = 101081103;
                break;
            case'胡尔勒   ':
                $id = 101081104;
                break;
            case'扎赉特旗  ':
                $id = 101081105;
                break;
            case'索伦    ':
                $id = 101081106;
                break;
            case'突泉    ':
                $id = 101081107;
                break;
            case'霍林郭勒  ':
                $id = 101081108;
                break;
            case'阿拉善左旗 ':
                $id = 101081201;
                break;
            case'阿拉善右旗 ':
                $id = 101081202;
                break;
            case'额济纳旗  ':
                $id = 101081203;
                break;
            case'拐子湖   ':
                $id = 101081204;
                break;
            case'吉兰太   ':
                $id = 101081205;
                break;
            case'锡林高勒  ':
                $id = 101081206;
                break;
            case'头道湖   ':
                $id = 101081207;
                break;
            case'中泉子   ':
                $id = 101081208;
                break;
            case'巴彦诺尔贡 ':
                $id = 101081209;
                break;
            case'雅布赖   ':
                $id = 101081210;
                break;
            case'乌斯太   ':
                $id = 101081211;
                break;
            case'孪井滩   ':
                $id = 101081212;
                break;

            case'石家庄   ':
                $id = 101090101;
                break;
            case'井陉    ':
                $id = 101090102;
                break;
            case'正定    ':
                $id = 101090103;
                break;
            case'栾城    ':
                $id = 101090104;
                break;
            case'行唐    ':
                $id = 101090105;
                break;
            case'灵寿    ':
                $id = 101090106;
                break;
            case'高邑    ':
                $id = 101090107;
                break;
            case'深泽    ':
                $id = 101090108;
                break;
            case'赞皇    ':
                $id = 101090109;
                break;
            case'无极    ':
                $id = 101090110;
                break;
            case'平山    ':
                $id = 101090111;
                break;
            case'元氏    ':
                $id = 101090112;
                break;
            case'赵县    ':
                $id = 101090113;
                break;
            case'辛集    ':
                $id = 101090114;
                break;
            case'藁城    ':
                $id = 101090115;
                break;
            case'晋洲    ':
                $id = 101090116;
                break;
            case'新乐    ':
                $id = 101090117;
                break;
            case'保定    ':
                $id = 101090201;
                break;
            case'满城    ':
                $id = 101090202;
                break;
            case'阜平    ':
                $id = 101090203;
                break;
            case'徐水    ':
                $id = 101090204;
                break;
            case'唐县    ':
                $id = 101090205;
                break;
            case'高阳    ':
                $id = 101090206;
                break;
            case'容城    ':
                $id = 101090207;
                break;
            case'紫荆关   ':
                $id = 101090208;
                break;
            case'涞源    ':
                $id = 101090209;
                break;
            case'望都    ':
                $id = 101090210;
                break;
            case'安新    ':
                $id = 101090211;
                break;
            case'易县    ':
                $id = 101090212;
                break;
            case'涞水    ':
                $id = 101090213;
                break;
            case'曲阳    ':
                $id = 101090214;
                break;
            case'蠡县    ':
                $id = 101090215;
                break;
            case'顺平    ':
                $id = 101090216;
                break;
            case'雄县    ':
                $id = 101090217;
                break;
            case'涿州    ':
                $id = 101090218;
                break;
            case'定州    ':
                $id = 101090219;
                break;
            case'安国    ':
                $id = 101090220;
                break;
            case'高碑店   ':
                $id = 101090221;
                break;
            case'张家口   ':
                $id = 101090301;
                break;
            case'宣化    ':
                $id = 101090302;
                break;
            case'张北    ':
                $id = 101090303;
                break;
            case'康保    ':
                $id = 101090304;
                break;
            case'沽源    ':
                $id = 101090305;
                break;
            case'尚义    ':
                $id = 101090306;
                break;
            case'蔚县    ':
                $id = 101090307;
                break;
            case'阳原    ':
                $id = 101090308;
                break;
            case'怀安    ':
                $id = 101090309;
                break;
            case'万全    ':
                $id = 101090310;
                break;
            case'怀来    ':
                $id = 101090311;
                break;
            case'涿鹿    ':
                $id = 101090312;
                break;
            case'赤城    ':
                $id = 101090313;
                break;
            case'崇礼    ':
                $id = 101090314;
                break;
            case'承德    ':
                $id = 101090402;
                break;
            case'承德县   ':
                $id = 101090403;
                break;
            case'兴隆    ':
                $id = 101090404;
                break;
            case'平泉    ':
                $id = 101090405;
                break;
            case'滦平    ':
                $id = 101090406;
                break;
            case'隆化    ':
                $id = 101090407;
                break;
            case'丰宁    ':
                $id = 101090408;
                break;
            case'宽城    ':
                $id = 101090409;
                break;
            case'围场    ':
                $id = 101090410;
                break;
            case'塞罕坎   ':
                $id = 101090411;
                break;
            case'唐山    ':
                $id = 101090501;
                break;
            case'丰南    ':
                $id = 101090502;
                break;
            case'丰润    ':
                $id = 101090503;
                break;
            case'滦县    ':
                $id = 101090504;
                break;
            case'滦南    ':
                $id = 101090505;
                break;
            case'乐亭    ':
                $id = 101090506;
                break;
            case'迁西    ':
                $id = 101090507;
                break;
            case'玉田    ':
                $id = 101090508;
                break;
            case'唐海    ':
                $id = 101090509;
                break;
            case'遵化    ':
                $id = 101090510;
                break;
            case'迁安    ':
                $id = 101090511;
                break;
            case'廊坊    ':
                $id = 101090601;
                break;
            case'固安    ':
                $id = 101090602;
                break;
            case'永清    ':
                $id = 101090603;
                break;
            case'香河    ':
                $id = 101090604;
                break;
            case'大城    ':
                $id = 101090605;
                break;
            case'文安    ':
                $id = 101090606;
                break;
            case'大厂    ':
                $id = 101090607;
                break;
            case'霸州    ':
                $id = 101090608;
                break;
            case'三河    ':
                $id = 101090609;
                break;
            case'沧州    ':
                $id = 101090701;
                break;
            case'青县    ':
                $id = 101090702;
                break;
            case'东光    ':
                $id = 101090703;
                break;
            case'海兴    ':
                $id = 101090704;
                break;
            case'盐山    ':
                $id = 101090705;
                break;
            case'肃宁    ':
                $id = 101090706;
                break;
            case'南皮    ':
                $id = 101090707;
                break;
            case'吴桥    ':
                $id = 101090708;
                break;
            case'献县    ':
                $id = 101090709;
                break;
            case'孟村    ':
                $id = 101090710;
                break;
            case'泊头    ':
                $id = 101090711;
                break;
            case'任丘    ':
                $id = 101090712;
                break;
            case'黄骅    ':
                $id = 101090713;
                break;
            case'河间    ':
                $id = 101090714;
                break;
            case'曹妃甸   ':
                $id = 101090715;
                break;
            case'衡水    ':
                $id = 101090801;
                break;
            case'枣强    ':
                $id = 101090802;
                break;
            case'武邑    ':
                $id = 101090803;
                break;
            case'武强    ':
                $id = 101090804;
                break;
            case'饶阳    ':
                $id = 101090805;
                break;
            case'安平    ':
                $id = 101090806;
                break;
            case'故城    ':
                $id = 101090807;
                break;
            case'景县    ':
                $id = 101090808;
                break;
            case'阜城    ':
                $id = 101090809;
                break;
            case'冀州    ':
                $id = 101090810;
                break;
            case'深州    ':
                $id = 101090811;
                break;
            case'邢台    ':
                $id = 101090901;
                break;
            case'临城    ':
                $id = 101090902;
                break;
            case'邢台县浆水 ':
                $id = 101090903;
                break;
            case'内邱    ':
                $id = 101090904;
                break;
            case'柏乡    ':
                $id = 101090905;
                break;
            case'隆尧    ':
                $id = 101090906;
                break;
            case'南和    ':
                $id = 101090907;
                break;
            case'宁晋    ':
                $id = 101090908;
                break;
            case'巨鹿    ':
                $id = 101090909;
                break;
            case'新河    ':
                $id = 101090910;
                break;
            case'广宗    ':
                $id = 101090911;
                break;
            case'平乡    ':
                $id = 101090912;
                break;
            case'威县    ':
                $id = 101090913;
                break;
            case'清河    ':
                $id = 101090914;
                break;
            case'临西    ':
                $id = 101090915;
                break;
            case'南宫    ':
                $id = 101090916;
                break;
            case'沙河    ':
                $id = 101090917;
                break;
            case'任县    ':
                $id = 101090918;
                break;
            case'邯郸    ':
                $id = 101091001;
                break;
            case'峰峰    ':
                $id = 101091002;
                break;
            case'临漳    ':
                $id = 101091003;
                break;
            case'成安    ':
                $id = 101091004;
                break;
            case'大名    ':
                $id = 101091005;
                break;
            case'涉县    ':
                $id = 101091006;
                break;
            case'磁县    ':
                $id = 101091007;
                break;
            case'肥乡    ':
                $id = 101091008;
                break;
            case'永年    ':
                $id = 101091009;
                break;
            case'邱县    ':
                $id = 101091010;
                break;
            case'鸡泽    ':
                $id = 101091011;
                break;
            case'广平    ':
                $id = 101091012;
                break;
            case'馆陶    ':
                $id = 101091013;
                break;
            case'魏县    ':
                $id = 101091014;
                break;
            case'曲周    ':
                $id = 101091015;
                break;
            case'武安    ':
                $id = 101091016;
                break;
            case'秦皇岛   ':
                $id = 101091101;
                break;
            case'青龙    ':
                $id = 101091102;
                break;
            case'昌黎    ':
                $id = 101091103;
                break;
            case'抚宁    ':
                $id = 101091104;
                break;
            case'卢龙    ':
                $id = 101091105;
                break;
            case'北戴河   ':
                $id = 101091106;
                break;

            case'太原    ':
                $id = 101100101;
                break;
            case'清徐    ':
                $id = 101100102;
                break;
            case'阳曲    ':
                $id = 101100103;
                break;
            case'娄烦    ':
                $id = 101100104;
                break;
            case'太原古交区 ':
                $id = 101100105;
                break;
            case'太原北郊  ':
                $id = 101100106;
                break;
            case'太原南郊  ':
                $id = 101100107;
                break;
            case'大同    ':
                $id = 101100201;
                break;
            case'阳高    ':
                $id = 101100202;
                break;
            case'大同县   ':
                $id = 101100203;
                break;
            case'天镇    ':
                $id = 101100204;
                break;
            case'广灵    ':
                $id = 101100205;
                break;
            case'灵邱    ':
                $id = 101100206;
                break;
            case'浑源    ':
                $id = 101100207;
                break;
            case'左云    ':
                $id = 101100208;
                break;
            case'阳泉    ':
                $id = 101100301;
                break;
            case'盂县    ':
                $id = 101100302;
                break;
            case'平定    ':
                $id = 101100303;
                break;
            case'晋中    ':
                $id = 101100401;
                break;
            case'榆次    ':
                $id = 101100402;
                break;
            case'榆社    ':
                $id = 101100403;
                break;
            case'左权    ':
                $id = 101100404;
                break;
            case'和顺    ':
                $id = 101100405;
                break;
            case'昔阳    ':
                $id = 101100406;
                break;
            case'寿阳    ':
                $id = 101100407;
                break;
            case'太谷    ':
                $id = 101100408;
                break;
            case'祁县    ':
                $id = 101100409;
                break;
            case'平遥    ':
                $id = 101100410;
                break;
            case'灵石    ':
                $id = 101100411;
                break;
            case'介休    ':
                $id = 101100412;
                break;
            case'长治    ':
                $id = 101100501;
                break;
            case'黎城    ':
                $id = 101100502;
                break;
            case'屯留    ':
                $id = 101100503;
                break;
            case'潞城    ':
                $id = 101100504;
                break;
            case'襄垣    ':
                $id = 101100505;
                break;
            case'平顺    ':
                $id = 101100506;
                break;
            case'武乡    ':
                $id = 101100507;
                break;
            case'沁县    ':
                $id = 101100508;
                break;
            case'长子    ':
                $id = 101100509;
                break;
            case'沁源    ':
                $id = 101100510;
                break;
            case'壶关    ':
                $id = 101100511;
                break;
            case'晋城    ':
                $id = 101100601;
                break;
            case'沁水    ':
                $id = 101100602;
                break;
            case'阳城    ':
                $id = 101100603;
                break;
            case'陵川    ':
                $id = 101100604;
                break;
            case'高平    ':
                $id = 101100605;
                break;
            case'临汾    ':
                $id = 101100701;
                break;
            case'曲沃    ':
                $id = 101100702;
                break;
            case'永和    ':
                $id = 101100703;
                break;
            case'隰县    ':
                $id = 101100704;
                break;
            case'大宁    ':
                $id = 101100705;
                break;
            case'吉县    ':
                $id = 101100706;
                break;
            case'襄汾    ':
                $id = 101100707;
                break;
            case'蒲县    ':
                $id = 101100708;
                break;
            case'汾西    ':
                $id = 101100709;
                break;
            case'洪洞    ':
                $id = 101100710;
                break;
            case'霍州    ':
                $id = 101100711;
                break;
            case'乡宁    ':
                $id = 101100712;
                break;
            case'翼城    ':
                $id = 101100713;
                break;
            case'侯马    ':
                $id = 101100714;
                break;
            case'浮山    ':
                $id = 101100715;
                break;
            case'安泽    ':
                $id = 101100716;
                break;
            case'古县    ':
                $id = 101100717;
                break;
            case'运城    ':
                $id = 101100801;
                break;
            case'临猗    ':
                $id = 101100802;
                break;
            case'稷山    ':
                $id = 101100803;
                break;
            case'万荣    ':
                $id = 101100804;
                break;
            case'河津    ':
                $id = 101100805;
                break;
            case'新绛    ':
                $id = 101100806;
                break;
            case'绛县    ':
                $id = 101100807;
                break;
            case'闻喜    ':
                $id = 101100808;
                break;
            case'垣曲    ':
                $id = 101100809;
                break;
            case'永济    ':
                $id = 101100810;
                break;
            case'芮城    ':
                $id = 101100811;
                break;
            case'夏县    ':
                $id = 101100812;
                break;
            case'平陆    ':
                $id = 101100813;
                break;
            case'朔州    ':
                $id = 101100901;
                break;
            case'平鲁    ':
                $id = 101100902;
                break;
            case'山阴    ':
                $id = 101100903;
                break;
            case'右玉    ':
                $id = 101100904;
                break;
            case'应县    ':
                $id = 101100905;
                break;
            case'怀仁    ':
                $id = 101100906;
                break;
            case'忻州    ':
                $id = 101101001;
                break;
            case'定襄    ':
                $id = 101101002;
                break;
            case'五台县豆村 ':
                $id = 101101003;
                break;
            case'河曲    ':
                $id = 101101004;
                break;
            case'偏关    ':
                $id = 101101005;
                break;
            case'神池    ':
                $id = 101101006;
                break;
            case'宁武    ':
                $id = 101101007;
                break;
            case'代县    ':
                $id = 101101008;
                break;
            case'繁峙    ':
                $id = 101101009;
                break;
            case'五台山   ':
                $id = 101101010;
                break;
            case'保德    ':
                $id = 101101011;
                break;
            case'静乐    ':
                $id = 101101012;
                break;
            case'岢岚    ':
                $id = 101101013;
                break;
            case'五寨    ':
                $id = 101101014;
                break;
            case'原平    ':
                $id = 101101015;
                break;
            case'吕梁    ':
                $id = 101101100;
                break;
            case'离石    ':
                $id = 101101101;
                break;
            case'临县    ':
                $id = 101101102;
                break;
            case'兴县    ':
                $id = 101101103;
                break;
            case'岚县    ':
                $id = 101101104;
                break;
            case'柳林    ':
                $id = 101101105;
                break;
            case'石楼    ':
                $id = 101101106;
                break;
            case'方山    ':
                $id = 101101107;
                break;
            case'交口    ':
                $id = 101101108;
                break;
            case'中阳    ':
                $id = 101101109;
                break;
            case'孝义    ':
                $id = 101101110;
                break;
            case'汾阳    ':
                $id = 101101111;
                break;
            case'文水    ':
                $id = 101101112;
                break;
            case'交城    ':
                $id = 101101113;
                break;

            case'西安    ':
                $id = 101110101;
                break;
            case'长安    ':
                $id = 101110102;
                break;
            case'临潼    ':
                $id = 101110103;
                break;
            case'蓝田    ':
                $id = 101110104;
                break;
            case'周至    ':
                $id = 101110105;
                break;
            case'户县    ':
                $id = 101110106;
                break;
            case'高陵    ':
                $id = 101110107;
                break;
            case'杨凌    ':
                $id = 101110108;
                break;
            case'咸阳    ':
                $id = 101110200;
                break;
            case'三原    ':
                $id = 101110201;
                break;
            case'礼泉    ':
                $id = 101110202;
                break;
            case'永寿    ':
                $id = 101110203;
                break;
            case'淳化    ':
                $id = 101110204;
                break;
            case'泾阳    ':
                $id = 101110205;
                break;
            case'武功    ':
                $id = 101110206;
                break;
            case'乾县    ':
                $id = 101110207;
                break;
            case'彬县    ':
                $id = 101110208;
                break;
            case'长武    ':
                $id = 101110209;
                break;
            case'旬邑    ':
                $id = 101110210;
                break;
            case'兴平    ':
                $id = 101110211;
                break;
            case'延安    ':
                $id = 101110300;
                break;
            case'延长    ':
                $id = 101110301;
                break;
            case'延川    ':
                $id = 101110302;
                break;
            case'子长    ':
                $id = 101110303;
                break;
            case'宜川    ':
                $id = 101110304;
                break;
            case'富县    ':
                $id = 101110305;
                break;
            case'志丹    ':
                $id = 101110306;
                break;
            case'安塞    ':
                $id = 101110307;
                break;
            case'甘泉    ':
                $id = 101110308;
                break;
            case'洛川    ':
                $id = 101110309;
                break;
            case'黄陵    ':
                $id = 101110310;
                break;
            case'黄龙    ':
                $id = 101110311;
                break;
            case'吴起    ':
                $id = 101110312;
                break;
            case'榆林    ':
                $id = 101110401;
                break;
            case'府谷    ':
                $id = 101110402;
                break;
            case'神木    ':
                $id = 101110403;
                break;
            case'佳县    ':
                $id = 101110404;
                break;
            case'定边    ':
                $id = 101110405;
                break;
            case'靖边    ':
                $id = 101110406;
                break;
            case'横山    ':
                $id = 101110407;
                break;
            case'米脂    ':
                $id = 101110408;
                break;
            case'子洲    ':
                $id = 101110409;
                break;
            case'绥德    ':
                $id = 101110410;
                break;
            case'吴堡    ':
                $id = 101110411;
                break;
            case'清涧    ':
                $id = 101110412;
                break;
            case'渭南    ':
                $id = 101110501;
                break;
            case'华县    ':
                $id = 101110502;
                break;
            case'潼关    ':
                $id = 101110503;
                break;
            case'大荔    ':
                $id = 101110504;
                break;
            case'白水    ':
                $id = 101110505;
                break;
            case'富平    ':
                $id = 101110506;
                break;
            case'蒲城    ':
                $id = 101110507;
                break;
            case'澄城    ':
                $id = 101110508;
                break;
            case'合阳    ':
                $id = 101110509;
                break;
            case'韩城    ':
                $id = 101110510;
                break;
            case'华阴    ':
                $id = 101110511;
                break;
            case'华山    ':
                $id = 101110512;
                break;
            case'商洛    ':
                $id = 101110601;
                break;
            case'洛南    ':
                $id = 101110602;
                break;
            case'柞水    ':
                $id = 101110603;
                break;
            case'镇安    ':
                $id = 101110605;
                break;
            case'丹凤    ':
                $id = 101110606;
                break;
            case'商南    ':
                $id = 101110607;
                break;
            case'山阳    ':
                $id = 101110608;
                break;
            case'安康    ':
                $id = 101110701;
                break;
            case'紫阳    ':
                $id = 101110702;
                break;
            case'石泉    ':
                $id = 101110703;
                break;
            case'汉阴    ':
                $id = 101110704;
                break;
            case'旬阳    ':
                $id = 101110705;
                break;
            case'岚皋    ':
                $id = 101110706;
                break;
            case'平利    ':
                $id = 101110707;
                break;
            case'白河    ':
                $id = 101110708;
                break;
            case'镇坪    ':
                $id = 101110709;
                break;
            case'宁陕    ':
                $id = 101110710;
                break;
            case'汉中    ':
                $id = 101110801;
                break;
            case'略阳    ':
                $id = 101110802;
                break;
            case'勉县    ':
                $id = 101110803;
                break;
            case'留坝    ':
                $id = 101110804;
                break;
            case'洋县    ':
                $id = 101110805;
                break;
            case'城固    ':
                $id = 101110806;
                break;
            case'西乡    ':
                $id = 101110807;
                break;
            case'佛坪    ':
                $id = 101110808;
                break;
            case'宁强    ':
                $id = 101110809;
                break;
            case'南郑    ':
                $id = 101110810;
                break;
            case'镇巴    ':
                $id = 101110811;
                break;
            case'宝鸡    ':
                $id = 101110901;
                break;
            case'宝鸡县   ':
                $id = 101110902;
                break;
            case'千阳    ':
                $id = 101110903;
                break;
            case'麟游    ':
                $id = 101110904;
                break;
            case'岐山    ':
                $id = 101110905;
                break;
            case'凤翔    ':
                $id = 101110906;
                break;
            case'扶风    ':
                $id = 101110907;
                break;
            case'眉县    ':
                $id = 101110908;
                break;
            case'太白    ':
                $id = 101110909;
                break;
            case'凤县    ':
                $id = 101110910;
                break;
            case'陇县    ':
                $id = 101110911;
                break;
            case'铜川    ':
                $id = 101111001;
                break;
            case'耀县    ':
                $id = 101111002;
                break;
            case'宜君    ':
                $id = 101111003;
                break;

            case'济南    ':
                $id = 101120101;
                break;
            case'长清    ':
                $id = 101120102;
                break;
            case'商河    ':
                $id = 101120103;
                break;
            case'章丘    ':
                $id = 101120104;
                break;
            case'平阴    ':
                $id = 101120105;
                break;
            case'济阳    ':
                $id = 101120106;
                break;
            case'青岛    ':
                $id = 101120201;
                break;
            case'崂山    ':
                $id = 101120202;
                break;
            case'潮连岛   ':
                $id = 101120203;
                break;
            case'即墨    ':
                $id = 101120204;
                break;
            case'胶州    ':
                $id = 101120205;
                break;
            case'胶南    ':
                $id = 101120206;
                break;
            case'莱西    ':
                $id = 101120207;
                break;
            case'平度    ':
                $id = 101120208;
                break;
            case'淄博    ':
                $id = 101120301;
                break;
            case'淄川    ':
                $id = 101120302;
                break;
            case'博山    ':
                $id = 101120303;
                break;
            case'高青    ':
                $id = 101120304;
                break;
            case'周村    ':
                $id = 101120305;
                break;
            case'沂源    ':
                $id = 101120306;
                break;
            case'桓台    ':
                $id = 101120307;
                break;
            case'临淄    ':
                $id = 101120308;
                break;
            case'德州    ':
                $id = 101120401;
                break;
            case'武城    ':
                $id = 101120402;
                break;
            case'临邑    ':
                $id = 101120403;
                break;
            case'陵县    ':
                $id = 101120404;
                break;
            case'齐河    ':
                $id = 101120405;
                break;
            case'乐陵    ':
                $id = 101120406;
                break;
            case'庆云    ':
                $id = 101120407;
                break;
            case'平原    ':
                $id = 101120408;
                break;
            case'宁津    ':
                $id = 101120409;
                break;
            case'夏津    ':
                $id = 101120410;
                break;
            case'禹城    ':
                $id = 101120411;
                break;
            case'烟台    ':
                $id = 101120501;
                break;
            case'莱州    ':
                $id = 101120502;
                break;
            case'长岛    ':
                $id = 101120503;
                break;
            case'蓬莱    ':
                $id = 101120504;
                break;
            case'龙口    ':
                $id = 101120505;
                break;
            case'招远    ':
                $id = 101120506;
                break;
            case'栖霞    ':
                $id = 101120507;
                break;
            case'福山    ':
                $id = 101120508;
                break;
            case'牟平    ':
                $id = 101120509;
                break;
            case'莱阳    ':
                $id = 101120510;
                break;
            case'海阳    ':
                $id = 101120511;
                break;
            case'千里岩   ':
                $id = 101120512;
                break;
            case'潍坊    ':
                $id = 101120601;
                break;
            case'青州    ':
                $id = 101120602;
                break;
            case'寿光    ':
                $id = 101120603;
                break;
            case'临朐    ':
                $id = 101120604;
                break;
            case'昌乐    ':
                $id = 101120605;
                break;
            case'昌邑    ':
                $id = 101120606;
                break;
            case'安丘    ':
                $id = 101120607;
                break;
            case'高密    ':
                $id = 101120608;
                break;
            case'诸城    ':
                $id = 101120609;
                break;
            case'济宁    ':
                $id = 101120701;
                break;
            case'嘉祥    ':
                $id = 101120702;
                break;
            case'微山    ':
                $id = 101120703;
                break;
            case'鱼台    ':
                $id = 101120704;
                break;
            case'兖州    ':
                $id = 101120705;
                break;
            case'金乡    ':
                $id = 101120706;
                break;
            case'汶上    ':
                $id = 101120707;
                break;
            case'泗水    ':
                $id = 101120708;
                break;
            case'梁山    ':
                $id = 101120709;
                break;
            case'曲阜    ':
                $id = 101120710;
                break;
            case'邹城    ':
                $id = 101120711;
                break;
            case'泰安    ':
                $id = 101120801;
                break;
            case'新泰    ':
                $id = 101120802;
                break;
            case'泰山    ':
                $id = 101120803;
                break;
            case'肥城    ':
                $id = 101120804;
                break;
            case'东平    ':
                $id = 101120805;
                break;
            case'宁阳    ':
                $id = 101120806;
                break;
            case'临沂    ':
                $id = 101120901;
                break;
            case'莒南    ':
                $id = 101120902;
                break;
            case'沂南    ':
                $id = 101120903;
                break;
            case'苍山    ':
                $id = 101120904;
                break;
            case'临沭    ':
                $id = 101120905;
                break;
            case'郯城    ':
                $id = 101120906;
                break;
            case'蒙阴    ':
                $id = 101120907;
                break;
            case'平邑    ':
                $id = 101120908;
                break;
            case'费县    ':
                $id = 101120909;
                break;
            case'沂水    ':
                $id = 101120910;
                break;
            case'马站    ':
                $id = 101120911;
                break;
            case'菏泽    ':
                $id = 101121001;
                break;
            case'鄄城    ':
                $id = 101121002;
                break;
            case'郓城    ':
                $id = 101121003;
                break;
            case'东明    ':
                $id = 101121004;
                break;
            case'定陶    ':
                $id = 101121005;
                break;
            case'巨野    ':
                $id = 101121006;
                break;
            case'曹县    ':
                $id = 101121007;
                break;
            case'成武    ':
                $id = 101121008;
                break;
            case'单县    ':
                $id = 101121009;
                break;
            case'滨州    ':
                $id = 101121101;
                break;
            case'博兴    ':
                $id = 101121102;
                break;
            case'无棣    ':
                $id = 101121103;
                break;
            case'阳信    ':
                $id = 101121104;
                break;
            case'惠民    ':
                $id = 101121105;
                break;
            case'沾化    ':
                $id = 101121106;
                break;
            case'邹平    ':
                $id = 101121107;
                break;
            case'东营    ':
                $id = 101121201;
                break;
            case'河口    ':
                $id = 101121202;
                break;
            case'垦利    ':
                $id = 101121203;
                break;
            case'利津    ':
                $id = 101121204;
                break;
            case'广饶    ':
                $id = 101121205;
                break;
            case'威海    ':
                $id = 101121301;
                break;
            case'文登    ':
                $id = 101121302;
                break;
            case'荣成    ':
                $id = 101121303;
                break;
            case'乳山    ':
                $id = 101121304;
                break;
            case'成山头   ':
                $id = 101121305;
                break;
            case'石岛    ':
                $id = 101121306;
                break;
            case'枣庄    ':
                $id = 101121401;
                break;
            case'薛城    ':
                $id = 101121402;
                break;
            case'峄城    ':
                $id = 101121403;
                break;
            case'台儿庄   ':
                $id = 101121404;
                break;
            case'滕州    ':
                $id = 101121405;
                break;
            case'日照    ':
                $id = 101121501;
                break;
            case'五莲    ':
                $id = 101121502;
                break;
            case'莒县    ':
                $id = 101121503;
                break;
            case'莱芜    ':
                $id = 101121601;
                break;
            case'聊城    ':
                $id = 101121701;
                break;
            case'冠县    ':
                $id = 101121702;
                break;
            case'阳谷    ':
                $id = 101121703;
                break;
            case'高唐    ':
                $id = 101121704;
                break;
            case'茌平    ':
                $id = 101121705;
                break;
            case'东阿    ':
                $id = 101121706;
                break;
            case'临清    ':
                $id = 101121707;
                break;
            case'朝城    ':
                $id = 101121708;
                break;
            case'莘县    ':
                $id = 101121709;
                break;

            case'乌鲁木齐  ':
                $id = 101130101;
                break;
            case'蔡家湖   ':
                $id = 101130102;
                break;
            case'小渠子   ':
                $id = 101130103;
                break;
            case'巴仑台   ':
                $id = 101130104;
                break;
            case'达坂城   ':
                $id = 101130105;
                break;
            case'十三间房气象站':
                $id = 101130106;
                break;
            case'天山大西沟 ':
                $id = 101130107;
                break;
            case'乌鲁木齐牧试站':
                $id = 101130108;
                break;
            case'天池    ':
                $id = 101130109;
                break;
            case'白杨沟   ':
                $id = 101130110;
                break;
            case'克拉玛依  ':
                $id = 101130201;
                break;
            case'石河子   ':
                $id = 101130301;
                break;
            case'炮台    ':
                $id = 101130302;
                break;
            case'莫索湾   ':
                $id = 101130303;
                break;
            case'乌兰乌苏  ':
                $id = 101130304;
                break;
            case'昌吉    ':
                $id = 101130401;
                break;
            case'呼图壁   ':
                $id = 101130402;
                break;
            case'米泉    ':
                $id = 101130403;
                break;
            case'阜康    ':
                $id = 101130404;
                break;
            case'吉木萨尔  ':
                $id = 101130405;
                break;
            case'奇台    ':
                $id = 101130406;
                break;
            case'玛纳斯   ':
                $id = 101130407;
                break;
            case'木垒    ':
                $id = 101130408;
                break;
            case'北塔山   ':
                $id = 101130409;
                break;
            case'吐鲁番   ':
                $id = 101130501;
                break;
            case'托克逊   ':
                $id = 101130502;
                break;
            case'吐鲁番东坎 ':
                $id = 101130503;
                break;
            case'鄯善    ':
                $id = 101130504;
                break;
            case'红柳河   ':
                $id = 101130505;
                break;
            case'库尔勒   ':
                $id = 101130601;
                break;
            case'轮台    ':
                $id = 101130602;
                break;
            case'尉犁    ':
                $id = 101130603;
                break;
            case'若羌    ':
                $id = 101130604;
                break;
            case'且末    ':
                $id = 101130605;
                break;
            case'和静    ':
                $id = 101130606;
                break;
            case'焉耆    ':
                $id = 101130607;
                break;
            case'和硕    ':
                $id = 101130608;
                break;
            case'库米什   ':
                $id = 101130609;
                break;
            case'巴音布鲁克 ':
                $id = 101130610;
                break;
            case'铁干里克  ':
                $id = 101130611;
                break;
            case'博湖    ':
                $id = 101130612;
                break;
            case'塔中    ':
                $id = 101130613;
                break;
            case'阿拉尔   ':
                $id = 101130701;
                break;
            case'阿克苏   ':
                $id = 101130801;
                break;
            case'乌什    ':
                $id = 101130802;
                break;
            case'温宿    ':
                $id = 101130803;
                break;
            case'拜城    ':
                $id = 101130804;
                break;
            case'新和    ':
                $id = 101130805;
                break;
            case'沙雅    ':
                $id = 101130806;
                break;
            case'库车    ':
                $id = 101130807;
                break;
            case'柯坪    ':
                $id = 101130808;
                break;
            case'阿瓦提   ':
                $id = 101130809;
                break;
            case'喀什    ':
                $id = 101130901;
                break;
            case'英吉沙   ':
                $id = 101130902;
                break;
            case'塔什库尔干 ':
                $id = 101130903;
                break;
            case'麦盖提   ':
                $id = 101130904;
                break;
            case'莎车    ':
                $id = 101130905;
                break;
            case'叶城    ':
                $id = 101130906;
                break;
            case'泽普    ':
                $id = 101130907;
                break;
            case'巴楚    ':
                $id = 101130908;
                break;
            case'岳普湖   ':
                $id = 101130909;
                break;
            case'伽师    ':
                $id = 101130910;
                break;
            case'伊宁    ':
                $id = 101131001;
                break;
            case'察布查尔  ':
                $id = 101131002;
                break;
            case'尼勒克   ':
                $id = 101131003;
                break;
            case'伊宁县   ':
                $id = 101131004;
                break;
            case'巩留    ':
                $id = 101131005;
                break;
            case'新源    ':
                $id = 101131006;
                break;
            case'昭苏    ':
                $id = 101131007;
                break;
            case'特克斯   ':
                $id = 101131008;
                break;
            case'霍城    ':
                $id = 101131009;
                break;
            case'霍尔果斯  ':
                $id = 101131010;
                break;
            case'塔城    ':
                $id = 101131101;
                break;
            case'裕民    ':
                $id = 101131102;
                break;
            case'额敏    ':
                $id = 101131103;
                break;
            case'和布克赛尔 ':
                $id = 101131104;
                break;
            case'托里    ':
                $id = 101131105;
                break;
            case'乌苏    ':
                $id = 101131106;
                break;
            case'沙湾    ':
                $id = 101131107;
                break;
            case'和丰    ':
                $id = 101131108;
                break;
            case'哈密    ':
                $id = 101131201;
                break;
            case'沁城    ':
                $id = 101131202;
                break;
            case'巴里坤   ':
                $id = 101131203;
                break;
            case'伊吾    ':
                $id = 101131204;
                break;
            case'淖毛湖   ':
                $id = 101131205;
                break;
            case'和田    ':
                $id = 101131301;
                break;
            case'皮山    ':
                $id = 101131302;
                break;
            case'策勒    ':
                $id = 101131303;
                break;
            case'墨玉    ':
                $id = 101131304;
                break;
            case'洛浦    ':
                $id = 101131305;
                break;
            case'民丰    ':
                $id = 101131306;
                break;
            case'于田    ':
                $id = 101131307;
                break;
            case'阿勒泰   ':
                $id = 101131401;
                break;
            case'哈巴河   ':
                $id = 101131402;
                break;
            case'一八五团  ':
                $id = 101131403;
                break;
            case'黑山头   ':
                $id = 101131404;
                break;
            case'吉木乃   ':
                $id = 101131405;
                break;
            case'布尔津   ':
                $id = 101131406;
                break;
            case'福海    ':
                $id = 101131407;
                break;
            case'富蕴    ':
                $id = 101131408;
                break;
            case'青河    ':
                $id = 101131409;
                break;
            case'安德河   ':
                $id = 101131410;
                break;
            case'阿图什   ':
                $id = 101131501;
                break;
            case'乌恰    ':
                $id = 101131502;
                break;
            case'阿克陶   ':
                $id = 101131503;
                break;
            case'阿合奇   ':
                $id = 101131504;
                break;
            case'吐尔尕特  ':
                $id = 101131505;
                break;
            case'博乐    ':
                $id = 101131601;
                break;
            case'温泉    ':
                $id = 101131602;
                break;
            case'精河    ':
                $id = 101131603;
                break;
            case'阿拉山口  ':
                $id = 101131606;
                break;

            case'拉萨    ':
                $id = 101140101;
                break;
            case'当雄    ':
                $id = 101140102;
                break;
            case'尼木    ':
                $id = 101140103;
                break;
            case'墨竹贡卡  ':
                $id = 101140104;
                break;
            case'日喀则   ':
                $id = 101140201;
                break;
            case'拉孜    ':
                $id = 101140202;
                break;
            case'南木林   ':
                $id = 101140203;
                break;
            case'聂拉木   ':
                $id = 101140204;
                break;
            case'定日    ':
                $id = 101140205;
                break;
            case'江孜    ':
                $id = 101140206;
                break;
            case'帕里    ':
                $id = 101140207;
                break;
            case'山南    ':
                $id = 101140301;
                break;
            case'贡嘎    ':
                $id = 101140302;
                break;
            case'琼结    ':
                $id = 101140303;
                break;
            case'加查    ':
                $id = 101140304;
                break;
            case'浪卡子   ':
                $id = 101140305;
                break;
            case'错那    ':
                $id = 101140306;
                break;
            case'隆子    ':
                $id = 101140307;
                break;
            case'泽当    ':
                $id = 101140308;
                break;
            case'林芝    ':
                $id = 101140401;
                break;
            case'波密    ':
                $id = 101140402;
                break;
            case'米林    ':
                $id = 101140403;
                break;
            case'察隅    ':
                $id = 101140404;
                break;
            case'昌都    ':
                $id = 101140501;
                break;
            case'丁青    ':
                $id = 101140502;
                break;
            case'类乌齐   ':
                $id = 101140503;
                break;
            case'洛隆    ':
                $id = 101140504;
                break;
            case'左贡    ':
                $id = 101140505;
                break;
            case'芒康    ':
                $id = 101140506;
                break;
            case'八宿    ':
                $id = 101140507;
                break;
            case'那曲    ':
                $id = 101140601;
                break;
            case'嘉黎    ':
                $id = 101140603;
                break;
            case'班戈    ':
                $id = 101140604;
                break;
            case'安多    ':
                $id = 101140605;
                break;
            case'索县    ':
                $id = 101140606;
                break;
            case'比如    ':
                $id = 101140607;
                break;
            case'阿里    ':
                $id = 101140701;
                break;
            case'改则    ':
                $id = 101140702;
                break;
            case'申扎    ':
                $id = 101140703;
                break;
            case'狮泉河   ':
                $id = 101140704;
                break;
            case'普兰    ':
                $id = 101140705;
                break;

            case'西宁    ':
                $id = 101150101;
                break;
            case'大通    ':
                $id = 101150102;
                break;
            case'湟源    ':
                $id = 101150103;
                break;
            case'湟中    ':
                $id = 101150104;
                break;
            case'铁卜加   ':
                $id = 101150105;
                break;
            case'铁卜加寺  ':
                $id = 101150106;
                break;
            case'中心站   ':
                $id = 101150107;
                break;
            case'海东    ':
                $id = 101150201;
                break;
            case'乐都    ':
                $id = 101150202;
                break;
            case'民和    ':
                $id = 101150203;
                break;
            case'互助    ':
                $id = 101150204;
                break;
            case'化隆    ':
                $id = 101150205;
                break;
            case'循化    ':
                $id = 101150206;
                break;
            case'冷湖    ':
                $id = 101150207;
                break;
            case'平安    ':
                $id = 101150208;
                break;
            case'黄南    ':
                $id = 101150301;
                break;
            case'尖扎    ':
                $id = 101150302;
                break;
            case'泽库    ':
                $id = 101150303;
                break;
            case'河南    ':
                $id = 101150304;
                break;
            case'海南    ':
                $id = 101150401;
                break;
            case'江西沟   ':
                $id = 101150402;
                break;
            case'贵德    ':
                $id = 101150404;
                break;
            case'河卡    ':
                $id = 101150405;
                break;
            case'兴海    ':
                $id = 101150406;
                break;
            case'贵南    ':
                $id = 101150407;
                break;
            case'同德    ':
                $id = 101150408;
                break;
            case'共和    ':
                $id = 101150409;
                break;
            case'果洛    ':
                $id = 101150501;
                break;
            case'班玛    ':
                $id = 101150502;
                break;
            case'甘德    ':
                $id = 101150503;
                break;
            case'达日    ':
                $id = 101150504;
                break;
            case'久治    ':
                $id = 101150505;
                break;
            case'玛多    ':
                $id = 101150506;
                break;
            case'清水河   ':
                $id = 101150507;
                break;
            case'玛沁    ':
                $id = 101150508;
                break;
            case'玉树    ':
                $id = 101150601;
                break;
            case'托托河   ':
                $id = 101150602;
                break;
            case'治多    ':
                $id = 101150603;
                break;
            case'杂多    ':
                $id = 101150604;
                break;
            case'囊谦    ':
                $id = 101150605;
                break;
            case'曲麻莱   ':
                $id = 101150606;
                break;
            case'海西    ':
                $id = 101150701;
                break;
            case'格尔木   ':
                $id = 101150702;
                break;
            case'察尔汉   ':
                $id = 101150703;
                break;
            case'野牛沟   ':
                $id = 101150704;
                break;
            case'五道梁   ':
                $id = 101150705;
                break;
            case'小灶火   ':
                $id = 101150706;
                break;
            case'天峻    ':
                $id = 101150708;
                break;
            case'乌兰    ':
                $id = 101150709;
                break;
            case'都兰    ':
                $id = 101150710;
                break;
            case'诺木洪   ':
                $id = 101150711;
                break;
            case'茫崖    ':
                $id = 101150712;
                break;
            case'大柴旦   ':
                $id = 101150713;
                break;
            case'茶卡    ':
                $id = 101150714;
                break;
            case'香日德   ':
                $id = 101150715;
                break;
            case'德令哈   ':
                $id = 101150716;
                break;
            case'海北    ':
                $id = 101150801;
                break;
            case'门源    ':
                $id = 101150802;
                break;
            case'祁连    ':
                $id = 101150803;
                break;
            case'海晏    ':
                $id = 101150804;
                break;
            case'托勒    ':
                $id = 101150805;
                break;
            case'刚察    ':
                $id = 101150806;
                break;

            case'兰州    ':
                $id = 101160101;
                break;
            case'皋兰    ':
                $id = 101160102;
                break;
            case'永登    ':
                $id = 101160103;
                break;
            case'榆中    ':
                $id = 101160104;
                break;
            case'定西    ':
                $id = 101160201;
                break;
            case'通渭    ':
                $id = 101160202;
                break;
            case'陇西    ':
                $id = 101160203;
                break;
            case'渭源    ':
                $id = 101160204;
                break;
            case'临洮    ':
                $id = 101160205;
                break;
            case'漳县    ':
                $id = 101160206;
                break;
            case'岷县    ':
                $id = 101160207;
                break;
            case'安定    ':
                $id = 101160208;
                break;
            case'平凉    ':
                $id = 101160301;
                break;
            case'泾川    ':
                $id = 101160302;
                break;
            case'灵台    ':
                $id = 101160303;
                break;
            case'崇信    ':
                $id = 101160304;
                break;
            case'华亭    ':
                $id = 101160305;
                break;
            case'庄浪    ':
                $id = 101160306;
                break;
            case'静宁    ':
                $id = 101160307;
                break;
            case'崆峒    ':
                $id = 101160308;
                break;
            case'庆阳    ':
                $id = 101160401;
                break;
            case'西峰    ':
                $id = 101160402;
                break;
            case'环县    ':
                $id = 101160403;
                break;
            case'华池    ':
                $id = 101160404;
                break;
            case'合水    ':
                $id = 101160405;
                break;
            case'正宁    ':
                $id = 101160406;
                break;
            case'宁县    ':
                $id = 101160407;
                break;
            case'镇原    ':
                $id = 101160408;
                break;
            case'庆城    ':
                $id = 101160409;
                break;
            case'武威    ':
                $id = 101160501;
                break;
            case'民勤    ':
                $id = 101160502;
                break;
            case'古浪    ':
                $id = 101160503;
                break;
            case'乌鞘岭   ':
                $id = 101160504;
                break;
            case'天祝    ':
                $id = 101160505;
                break;
            case'金昌    ':
                $id = 101160601;
                break;
            case'永昌    ':
                $id = 101160602;
                break;
            case'张掖    ':
                $id = 101160701;
                break;
            case'肃南    ':
                $id = 101160702;
                break;
            case'民乐    ':
                $id = 101160703;
                break;
            case'临泽    ':
                $id = 101160704;
                break;
            case'高台    ':
                $id = 101160705;
                break;
            case'山丹    ':
                $id = 101160706;
                break;
            case'酒泉    ':
                $id = 101160801;
                break;
            case'鼎新    ':
                $id = 101160802;
                break;
            case'金塔    ':
                $id = 101160803;
                break;
            case'马鬃山   ':
                $id = 101160804;
                break;
            case'瓜州    ':
                $id = 101160805;
                break;
            case'肃北    ':
                $id = 101160806;
                break;
            case'玉门镇   ':
                $id = 101160807;
                break;
            case'敦煌    ':
                $id = 101160808;
                break;
            case'天水    ':
                $id = 101160901;
                break;
            case'北道区   ':
                $id = 101160902;
                break;
            case'清水    ':
                $id = 101160903;
                break;
            case'秦安    ':
                $id = 101160904;
                break;
            case'甘谷    ':
                $id = 101160905;
                break;
            case'武山    ':
                $id = 101160906;
                break;
            case'张家川   ':
                $id = 101160907;
                break;
            case'麦积    ':
                $id = 101160908;
                break;
            case'武都    ':
                $id = 101161001;
                break;
            case'成县    ':
                $id = 101161002;
                break;
            case'文县    ':
                $id = 101161003;
                break;
            case'宕昌    ':
                $id = 101161004;
                break;
            case'康县    ':
                $id = 101161005;
                break;
            case'西和    ':
                $id = 101161006;
                break;
            case'礼县    ':
                $id = 101161007;
                break;
            case'徽县    ':
                $id = 101161008;
                break;
            case'两当    ':
                $id = 101161009;
                break;
            case'临夏    ':
                $id = 101161101;
                break;
            case'康乐    ':
                $id = 101161102;
                break;
            case'永靖    ':
                $id = 101161103;
                break;
            case'广河    ':
                $id = 101161104;
                break;
            case'和政    ':
                $id = 101161105;
                break;
            case'东乡    ':
                $id = 101161106;
                break;
            case'合作    ':
                $id = 101161201;
                break;
            case'临潭    ':
                $id = 101161202;
                break;
            case'卓尼    ':
                $id = 101161203;
                break;
            case'舟曲    ':
                $id = 101161204;
                break;
            case'迭部    ':
                $id = 101161205;
                break;
            case'玛曲    ':
                $id = 101161206;
                break;
            case'碌曲    ':
                $id = 101161207;
                break;
            case'夏河    ':
                $id = 101161208;
                break;
            case'白银    ':
                $id = 101161301;
                break;
            case'靖远    ':
                $id = 101161302;
                break;
            case'会宁    ':
                $id = 101161303;
                break;
            case'华家岭   ':
                $id = 101161304;
                break;
            case'景泰    ':
                $id = 101161305;
                break;

            case'银川    ':
                $id = 101170101;
                break;
            case'永宁    ':
                $id = 101170102;
                break;
            case'灵武    ':
                $id = 101170103;
                break;
            case'贺兰    ':
                $id = 101170104;
                break;
            case'石嘴山   ':
                $id = 101170201;
                break;
            case'惠农    ':
                $id = 101170202;
                break;
            case'平罗    ':
                $id = 101170203;
                break;
            case'陶乐    ':
                $id = 101170204;
                break;
            case'石炭井   ':
                $id = 101170205;
                break;
            case'大武口   ':
                $id = 101170206;
                break;
            case'吴忠    ':
                $id = 101170301;
                break;
            case'同心    ':
                $id = 101170302;
                break;
            case'盐池    ':
                $id = 101170303;
                break;
            case'韦州    ':
                $id = 101170304;
                break;
            case'麻黄山   ':
                $id = 101170305;
                break;
            case'青铜峡   ':
                $id = 101170306;
                break;
            case'固原    ':
                $id = 101170401;
                break;
            case'西吉    ':
                $id = 101170402;
                break;
            case'隆德    ':
                $id = 101170403;
                break;
            case'泾源    ':
                $id = 101170404;
                break;
            case'六盘山   ':
                $id = 101170405;
                break;
            case'彭阳    ':
                $id = 101170406;
                break;
            case'中卫    ':
                $id = 101170501;
                break;
            case'中宁    ':
                $id = 101170502;
                break;
            case'兴仁堡   ':
                $id = 101170503;
                break;
            case'海原    ':
                $id = 101170504;
                break;

            case'郑州    ':
                $id = 101180101;
                break;
            case'巩义    ':
                $id = 101180102;
                break;
            case'荥阳    ':
                $id = 101180103;
                break;
            case'登封    ':
                $id = 101180104;
                break;
            case'新密    ':
                $id = 101180105;
                break;
            case'新郑    ':
                $id = 101180106;
                break;
            case'中牟    ':
                $id = 101180107;
                break;
            case'郑州农试站 ':
                $id = 101180108;
                break;
            case'安阳    ':
                $id = 101180201;
                break;
            case'汤阴    ':
                $id = 101180202;
                break;
            case'滑县    ':
                $id = 101180203;
                break;
            case'内黄    ':
                $id = 101180204;
                break;
            case'林州    ':
                $id = 101180205;
                break;
            case'新乡    ':
                $id = 101180301;
                break;
            case'获嘉    ':
                $id = 101180302;
                break;
            case'原阳    ':
                $id = 101180303;
                break;
            case'辉县    ':
                $id = 101180304;
                break;
            case'卫辉    ':
                $id = 101180305;
                break;
            case'延津    ':
                $id = 101180306;
                break;
            case'封丘    ':
                $id = 101180307;
                break;
            case'长垣    ':
                $id = 101180308;
                break;
            case'许昌    ':
                $id = 101180401;
                break;
            case'鄢陵    ':
                $id = 101180402;
                break;
            case'襄城    ':
                $id = 101180403;
                break;
            case'长葛    ':
                $id = 101180404;
                break;
            case'禹州    ':
                $id = 101180405;
                break;
            case'平顶山   ':
                $id = 101180501;
                break;
            case'郏县    ':
                $id = 101180502;
                break;
            case'宝丰    ':
                $id = 101180503;
                break;
            case'汝州    ':
                $id = 101180504;
                break;
            case'叶县    ':
                $id = 101180505;
                break;
            case'舞钢    ':
                $id = 101180506;
                break;
            case'鲁山    ':
                $id = 101180507;
                break;
            case'信阳    ':
                $id = 101180601;
                break;
            case'息县    ':
                $id = 101180602;
                break;
            case'罗山    ':
                $id = 101180603;
                break;
            case'光山    ':
                $id = 101180604;
                break;
            case'新县    ':
                $id = 101180605;
                break;
            case'淮滨    ':
                $id = 101180606;
                break;
            case'潢川    ':
                $id = 101180607;
                break;
            case'固始    ':
                $id = 101180608;
                break;
            case'商城    ':
                $id = 101180609;
                break;
            case'鸡公山   ':
                $id = 101180610;
                break;
            case'信阳地区农试站':
                $id = 101180611;
                break;
            case'南阳    ':
                $id = 101180701;
                break;
            case'南召    ':
                $id = 101180702;
                break;
            case'方城    ':
                $id = 101180703;
                break;
            case'社旗    ':
                $id = 101180704;
                break;
            case'西峡    ':
                $id = 101180705;
                break;
            case'内乡    ':
                $id = 101180706;
                break;
            case'镇平    ':
                $id = 101180707;
                break;
            case'淅川    ':
                $id = 101180708;
                break;
            case'新野    ':
                $id = 101180709;
                break;
            case'唐河    ':
                $id = 101180710;
                break;
            case'邓州    ':
                $id = 101180711;
                break;
            case'桐柏    ':
                $id = 101180712;
                break;
            case'开封    ':
                $id = 101180801;
                break;
            case'杞县    ':
                $id = 101180802;
                break;
            case'尉氏    ':
                $id = 101180803;
                break;
            case'通许    ':
                $id = 101180804;
                break;
            case'兰考    ':
                $id = 101180805;
                break;
            case'洛阳    ':
                $id = 101180901;
                break;
            case'新安    ':
                $id = 101180902;
                break;
            case'孟津    ':
                $id = 101180903;
                break;
            case'宜阳    ':
                $id = 101180904;
                break;
            case'洛宁    ':
                $id = 101180905;
                break;
            case'伊川    ':
                $id = 101180906;
                break;
            case'嵩县    ':
                $id = 101180907;
                break;
            case'偃师    ':
                $id = 101180908;
                break;
            case'栾川    ':
                $id = 101180909;
                break;
            case'汝阳    ':
                $id = 101180910;
                break;
            case'商丘    ':
                $id = 101181001;
                break;
            case'睢阳区   ':
                $id = 101181002;
                break;
            case'睢县    ':
                $id = 101181003;
                break;
            case'民权    ':
                $id = 101181004;
                break;
            case'虞城    ':
                $id = 101181005;
                break;
            case'柘城    ':
                $id = 101181006;
                break;
            case'宁陵    ':
                $id = 101181007;
                break;
            case'夏邑    ':
                $id = 101181008;
                break;
            case'永城    ':
                $id = 101181009;
                break;
            case'焦作    ':
                $id = 101181101;
                break;
            case'修武    ':
                $id = 101181102;
                break;
            case'武陟    ':
                $id = 101181103;
                break;
            case'沁阳    ':
                $id = 101181104;
                break;
            case'博爱    ':
                $id = 101181106;
                break;
            case'温县    ':
                $id = 101181107;
                break;
            case'孟州    ':
                $id = 101181108;
                break;
            case'鹤壁    ':
                $id = 101181201;
                break;
            case'浚县    ':
                $id = 101181202;
                break;
            case'淇县    ':
                $id = 101181203;
                break;
            case'濮阳    ':
                $id = 101181301;
                break;
            case'台前    ':
                $id = 101181302;
                break;
            case'南乐    ':
                $id = 101181303;
                break;
            case'清丰    ':
                $id = 101181304;
                break;
            case'范县    ':
                $id = 101181305;
                break;
            case'周口    ':
                $id = 101181401;
                break;
            case'扶沟    ':
                $id = 101181402;
                break;
            case'太康    ':
                $id = 101181403;
                break;
            case'淮阳    ':
                $id = 101181404;
                break;
            case'西华    ':
                $id = 101181405;
                break;
            case'商水    ':
                $id = 101181406;
                break;
            case'项城    ':
                $id = 101181407;
                break;
            case'郸城    ':
                $id = 101181408;
                break;
            case'鹿邑    ':
                $id = 101181409;
                break;
            case'沈丘    ':
                $id = 101181410;
                break;
            case'黄泛区   ':
                $id = 101181411;
                break;
            case'漯河    ':
                $id = 101181501;
                break;
            case'临颍    ':
                $id = 101181502;
                break;
            case'舞阳    ':
                $id = 101181503;
                break;
            case'驻马店   ':
                $id = 101181601;
                break;
            case'西平    ':
                $id = 101181602;
                break;
            case'遂平    ':
                $id = 101181603;
                break;
            case'上蔡    ':
                $id = 101181604;
                break;
            case'汝南    ':
                $id = 101181605;
                break;
            case'泌阳    ':
                $id = 101181606;
                break;
            case'平舆    ':
                $id = 101181607;
                break;
            case'新蔡    ':
                $id = 101181608;
                break;
            case'确山    ':
                $id = 101181609;
                break;
            case'正阳    ':
                $id = 101181610;
                break;
            case'三门峡   ':
                $id = 101181701;
                break;
            case'灵宝    ':
                $id = 101181702;
                break;
            case'渑池    ':
                $id = 101181703;
                break;
            case'卢氏    ':
                $id = 101181704;
                break;
            case'济源    ':
                $id = 101181801;
                break;

            case'南京    ':
                $id = 101190101;
                break;
            case'溧水    ':
                $id = 101190102;
                break;
            case'高淳    ':
                $id = 101190103;
                break;
            case'江宁    ':
                $id = 101190104;
                break;
            case'六合    ':
                $id = 101190105;
                break;
            case'江浦    ':
                $id = 101190106;
                break;
            case'浦口    ':
                $id = 101190107;
                break;
            case'无锡    ':
                $id = 101190201;
                break;
            case'江阴    ':
                $id = 101190202;
                break;
            case'宜兴    ':
                $id = 101190203;
                break;
            case'镇江    ':
                $id = 101190301;
                break;
            case'丹阳    ':
                $id = 101190302;
                break;
            case'扬中    ':
                $id = 101190303;
                break;
            case'句容    ':
                $id = 101190304;
                break;
            case'丹徒    ':
                $id = 101190305;
                break;
            case'苏州    ':
                $id = 101190401;
                break;
            case'常熟    ':
                $id = 101190402;
                break;
            case'张家港   ':
                $id = 101190403;
                break;
            case'昆山    ':
                $id = 101190404;
                break;
            case'吴县东山  ':
                $id = 101190405;
                break;
            case'吴县    ':
                $id = 101190406;
                break;
            case'吴江    ':
                $id = 101190407;
                break;
            case'太仓    ':
                $id = 101190408;
                break;
            case'南通    ':
                $id = 101190501;
                break;
            case'海安    ':
                $id = 101190502;
                break;
            case'如皋    ':
                $id = 101190503;
                break;
            case'如东    ':
                $id = 101190504;
                break;
            case'吕泗    ':
                $id = 101190505;
                break;
            case'吕泗渔场  ':
                $id = 101190506;
                break;
            case'启东    ':
                $id = 101190507;
                break;
            case'海门    ':
                $id = 101190508;
                break;
            case'通州    ':
                $id = 101190509;
                break;
            case'扬州    ':
                $id = 101190601;
                break;
            case'宝应    ':
                $id = 101190602;
                break;
            case'仪征    ':
                $id = 101190603;
                break;
            case'高邮    ':
                $id = 101190604;
                break;
            case'江都    ':
                $id = 101190605;
                break;
            case'邗江    ':
                $id = 101190606;
                break;
            case'盐城    ':
                $id = 101190701;
                break;
            case'响水    ':
                $id = 101190702;
                break;
            case'滨海    ':
                $id = 101190703;
                break;
            case'阜宁    ':
                $id = 101190704;
                break;
            case'射阳    ':
                $id = 101190705;
                break;
            case'建湖    ':
                $id = 101190706;
                break;
            case'东台    ':
                $id = 101190707;
                break;
            case'大丰    ':
                $id = 101190708;
                break;
            case'盐都    ':
                $id = 101190709;
                break;
            case'徐州    ':
                $id = 101190801;
                break;
            case'徐州农试站 ':
                $id = 101190802;
                break;
            case'丰县    ':
                $id = 101190803;
                break;
            case'沛县    ':
                $id = 101190804;
                break;
            case'邳州    ':
                $id = 101190805;
                break;
            case'睢宁    ':
                $id = 101190806;
                break;
            case'新沂    ':
                $id = 101190807;
                break;
            case'淮安    ':
                $id = 101190901;
                break;
            case'金湖    ':
                $id = 101190902;
                break;
            case'盱眙    ':
                $id = 101190903;
                break;
            case'洪泽    ':
                $id = 101190904;
                break;
            case'涟水    ':
                $id = 101190905;
                break;
            case'淮阴县   ':
                $id = 101190906;
                break;
            case'淮阴    ':
                $id = 101190907;
                break;
            case'楚州    ':
                $id = 101190908;
                break;
            case'连云港   ':
                $id = 101191001;
                break;
            case'东海    ':
                $id = 101191002;
                break;
            case'赣榆    ':
                $id = 101191003;
                break;
            case'灌云    ':
                $id = 101191004;
                break;
            case'灌南    ':
                $id = 101191005;
                break;
            case'西连岛   ':
                $id = 101191006;
                break;
            case'燕尾港   ':
                $id = 101191007;
                break;
            case'常州    ':
                $id = 101191101;
                break;
            case'溧阳    ':
                $id = 101191102;
                break;
            case'金坛    ':
                $id = 101191103;
                break;
            case'泰州    ':
                $id = 101191201;
                break;
            case'兴化    ':
                $id = 101191202;
                break;
            case'泰兴    ':
                $id = 101191203;
                break;
            case'姜堰    ':
                $id = 101191204;
                break;
            case'靖江    ':
                $id = 101191205;
                break;
            case'宿迁    ':
                $id = 101191301;
                break;
            case'沭阳    ':
                $id = 101191302;
                break;
            case'泗阳    ':
                $id = 101191303;
                break;
            case'泗洪    ':
                $id = 101191304;
                break;

            case'武汉    ':
                $id = 101200101;
                break;
            case'蔡甸    ':
                $id = 101200102;
                break;
            case'黄陂    ':
                $id = 101200103;
                break;
            case'新洲    ':
                $id = 101200104;
                break;
            case'江夏    ':
                $id = 101200105;
                break;
            case'襄樊    ':
                $id = 101200201;
                break;
            case'襄阳    ':
                $id = 101200202;
                break;
            case'保康    ':
                $id = 101200203;
                break;
            case'南漳    ':
                $id = 101200204;
                break;
            case'宜城    ':
                $id = 101200205;
                break;
            case'老河口   ':
                $id = 101200206;
                break;
            case'谷城    ':
                $id = 101200207;
                break;
            case'枣阳    ':
                $id = 101200208;
                break;
            case'鄂州    ':
                $id = 101200301;
                break;
            case'孝感    ':
                $id = 101200401;
                break;
            case'安陆    ':
                $id = 101200402;
                break;
            case'云梦    ':
                $id = 101200403;
                break;
            case'大悟    ':
                $id = 101200404;
                break;
            case'应城    ':
                $id = 101200405;
                break;
            case'汉川    ':
                $id = 101200406;
                break;
            case'黄冈    ':
                $id = 101200501;
                break;
            case'红安    ':
                $id = 101200502;
                break;
            case'麻城    ':
                $id = 101200503;
                break;
            case'罗田    ':
                $id = 101200504;
                break;
            case'英山    ':
                $id = 101200505;
                break;
            case'浠水    ':
                $id = 101200506;
                break;
            case'蕲春    ':
                $id = 101200507;
                break;
            case'黄梅    ':
                $id = 101200508;
                break;
            case'武穴    ':
                $id = 101200509;
                break;
            case'黄石    ':
                $id = 101200601;
                break;
            case'大冶    ':
                $id = 101200602;
                break;
            case'阳新    ':
                $id = 101200603;
                break;
            case'咸宁    ':
                $id = 101200701;
                break;
            case'赤壁    ':
                $id = 101200702;
                break;
            case'嘉鱼    ':
                $id = 101200703;
                break;
            case'崇阳    ':
                $id = 101200704;
                break;
            case'通城    ':
                $id = 101200705;
                break;
            case'通山    ':
                $id = 101200706;
                break;
            case'荆州    ':
                $id = 101200801;
                break;
            case'江陵    ':
                $id = 101200802;
                break;
            case'公安    ':
                $id = 101200803;
                break;
            case'石首    ':
                $id = 101200804;
                break;
            case'监利    ':
                $id = 101200805;
                break;
            case'洪湖    ':
                $id = 101200806;
                break;
            case'松滋    ':
                $id = 101200807;
                break;
            case'宜昌    ':
                $id = 101200901;
                break;
            case'远安    ':
                $id = 101200902;
                break;
            case'秭归    ':
                $id = 101200903;
                break;
            case'兴山    ':
                $id = 101200904;
                break;
            case'宜昌县   ':
                $id = 101200905;
                break;
            case'五峰    ':
                $id = 101200906;
                break;
            case'当阳    ':
                $id = 101200907;
                break;
            case'长阳    ':
                $id = 101200908;
                break;
            case'宜都    ':
                $id = 101200909;
                break;
            case'枝江    ':
                $id = 101200910;
                break;
            case'三峡    ':
                $id = 101200911;
                break;
            case'夷陵    ':
                $id = 101200912;
                break;
            case'恩施    ':
                $id = 101201001;
                break;
            case'利川    ':
                $id = 101201002;
                break;
            case'建始    ':
                $id = 101201003;
                break;
            case'咸丰    ':
                $id = 101201004;
                break;
            case'宣恩    ':
                $id = 101201005;
                break;
            case'鹤峰    ':
                $id = 101201006;
                break;
            case'来凤    ':
                $id = 101201007;
                break;
            case'巴东    ':
                $id = 101201008;
                break;
            case'绿葱坡   ':
                $id = 101201009;
                break;
            case'十堰    ':
                $id = 101201101;
                break;
            case'竹溪    ':
                $id = 101201102;
                break;
            case'郧西    ':
                $id = 101201103;
                break;
            case'郧县    ':
                $id = 101201104;
                break;
            case'竹山    ':
                $id = 101201105;
                break;
            case'房县    ':
                $id = 101201106;
                break;
            case'丹江口   ':
                $id = 101201107;
                break;
            case'神农架   ':
                $id = 101201201;
                break;
            case'随州    ':
                $id = 101201301;
                break;
            case'广水    ':
                $id = 101201302;
                break;
            case'荆门    ':
                $id = 101201401;
                break;
            case'钟祥    ':
                $id = 101201402;
                break;
            case'京山    ':
                $id = 101201403;
                break;
            case'天门    ':
                $id = 101201501;
                break;
            case'仙桃    ':
                $id = 101201601;
                break;
            case'潜江    ':
                $id = 101201701;
                break;

            case'杭州    ':
                $id = 101210101;
                break;
            case'萧山    ':
                $id = 101210102;
                break;
            case'桐庐    ':
                $id = 101210103;
                break;
            case'淳安    ':
                $id = 101210104;
                break;
            case'建德    ':
                $id = 101210105;
                break;
            case'余杭    ':
                $id = 101210106;
                break;
            case'临安    ':
                $id = 101210107;
                break;
            case'富阳    ':
                $id = 101210108;
                break;
            case'湖州    ':
                $id = 101210201;
                break;
            case'长兴    ':
                $id = 101210202;
                break;
            case'安吉    ':
                $id = 101210203;
                break;
            case'德清    ':
                $id = 101210204;
                break;
            case'嘉兴    ':
                $id = 101210301;
                break;
            case'嘉善    ':
                $id = 101210302;
                break;
            case'海宁    ':
                $id = 101210303;
                break;
            case'桐乡    ':
                $id = 101210304;
                break;
            case'平湖    ':
                $id = 101210305;
                break;
            case'海盐    ':
                $id = 101210306;
                break;
            case'宁波    ':
                $id = 101210401;
                break;
            case'慈溪    ':
                $id = 101210403;
                break;
            case'余姚    ':
                $id = 101210404;
                break;
            case'奉化    ':
                $id = 101210405;
                break;
            case'象山    ':
                $id = 101210406;
                break;
            case'石浦    ':
                $id = 101210407;
                break;
            case'宁海    ':
                $id = 101210408;
                break;
            case'鄞县    ':
                $id = 101210409;
                break;
            case'北仑    ':
                $id = 101210410;
                break;
            case'鄞州    ':
                $id = 101210411;
                break;
            case'镇海    ':
                $id = 101210412;
                break;
            case'绍兴    ':
                $id = 101210501;
                break;
            case'诸暨    ':
                $id = 101210502;
                break;
            case'上虞    ':
                $id = 101210503;
                break;
            case'新昌    ':
                $id = 101210504;
                break;
            case'嵊州    ':
                $id = 101210505;
                break;
            case'台州    ':
                $id = 101210601;
                break;
            case'括苍山   ':
                $id = 101210602;
                break;
            case'玉环    ':
                $id = 101210603;
                break;
            case'三门    ':
                $id = 101210604;
                break;
            case'天台    ':
                $id = 101210605;
                break;
            case'仙居    ':
                $id = 101210606;
                break;
            case'温岭    ':
                $id = 101210607;
                break;
            case'大陈    ':
                $id = 101210608;
                break;
            case'洪家    ':
                $id = 101210609;
                break;
            case'温州    ':
                $id = 101210701;
                break;
            case'泰顺    ':
                $id = 101210702;
                break;
            case'文成    ':
                $id = 101210703;
                break;
            case'平阳    ':
                $id = 101210704;
                break;
            case'瑞安    ':
                $id = 101210705;
                break;
            case'洞头    ':
                $id = 101210706;
                break;
            case'乐清    ':
                $id = 101210707;
                break;
            case'永嘉    ':
                $id = 101210708;
                break;
            case'苍南    ':
                $id = 101210709;
                break;
            case'丽水    ':
                $id = 101210801;
                break;
            case'遂昌    ':
                $id = 101210802;
                break;
            case'龙泉    ':
                $id = 101210803;
                break;
            case'缙云    ':
                $id = 101210804;
                break;
            case'青田    ':
                $id = 101210805;
                break;
            case'云和    ':
                $id = 101210806;
                break;
            case'庆元    ':
                $id = 101210807;
                break;
            case'金华    ':
                $id = 101210901;
                break;
            case'浦江    ':
                $id = 101210902;
                break;
            case'兰溪    ':
                $id = 101210903;
                break;
            case'义乌    ':
                $id = 101210904;
                break;
            case'东阳    ':
                $id = 101210905;
                break;
            case'武义    ':
                $id = 101210906;
                break;
            case'永康    ':
                $id = 101210907;
                break;
            case'磐安    ':
                $id = 101210908;
                break;
            case'衢州    ':
                $id = 101211001;
                break;
            case'常山    ':
                $id = 101211002;
                break;
            case'开化    ':
                $id = 101211003;
                break;
            case'龙游    ':
                $id = 101211004;
                break;
            case'江山    ':
                $id = 101211005;
                break;
            case'舟山    ':
                $id = 101211101;
                break;
            case'嵊泗    ':
                $id = 101211102;
                break;
            case'嵊山    ':
                $id = 101211103;
                break;
            case'岱山    ':
                $id = 101211104;
                break;
            case'普陀    ':
                $id = 101211105;
                break;
            case'定海    ':
                $id = 101211106;
                break;

            case'合肥    ':
                $id = 101220101;
                break;
            case'长丰    ':
                $id = 101220102;
                break;
            case'肥东    ':
                $id = 101220103;
                break;
            case'肥西    ':
                $id = 101220104;
                break;
            case'蚌埠    ':
                $id = 101220201;
                break;
            case'怀远    ':
                $id = 101220202;
                break;
            case'固镇    ':
                $id = 101220203;
                break;
            case'五河    ':
                $id = 101220204;
                break;
            case'芜湖    ':
                $id = 101220301;
                break;
            case'繁昌    ':
                $id = 101220302;
                break;
            case'芜湖县   ':
                $id = 101220303;
                break;
            case'南陵    ':
                $id = 101220304;
                break;
            case'淮南    ':
                $id = 101220401;
                break;
            case'凤台    ':
                $id = 101220402;
                break;
            case'马鞍山   ':
                $id = 101220501;
                break;
            case'当涂    ':
                $id = 101220502;
                break;
            case'安庆    ':
                $id = 101220601;
                break;
            case'枞阳    ':
                $id = 101220602;
                break;
            case'太湖    ':
                $id = 101220603;
                break;
            case'潜山    ':
                $id = 101220604;
                break;
            case'怀宁    ':
                $id = 101220605;
                break;
            case'宿松    ':
                $id = 101220606;
                break;
            case'望江    ':
                $id = 101220607;
                break;
            case'岳西    ':
                $id = 101220608;
                break;
            case'桐城    ':
                $id = 101220609;
                break;
            case'宿州    ':
                $id = 101220701;
                break;
            case'砀山    ':
                $id = 101220702;
                break;
            case'灵璧    ':
                $id = 101220703;
                break;
            case'泗县    ':
                $id = 101220704;
                break;
            case'萧县    ':
                $id = 101220705;
                break;
            case'阜阳    ':
                $id = 101220801;
                break;
            case'阜南    ':
                $id = 101220802;
                break;
            case'颍上    ':
                $id = 101220803;
                break;
            case'临泉    ':
                $id = 101220804;
                break;
            case'界首    ':
                $id = 101220805;
                break;
            case'太和    ':
                $id = 101220806;
                break;
            case'亳州    ':
                $id = 101220901;
                break;
            case'涡阳    ':
                $id = 101220902;
                break;
            case'利辛    ':
                $id = 101220903;
                break;
            case'蒙城    ':
                $id = 101220904;
                break;
            case'黄山站   ':
                $id = 101221001;
                break;
            case'黄山区   ':
                $id = 101221002;
                break;
            case'屯溪    ':
                $id = 101221003;
                break;
            case'祁门    ':
                $id = 101221004;
                break;
            case'黟县    ':
                $id = 101221005;
                break;
            case'歙县    ':
                $id = 101221006;
                break;
            case'休宁    ':
                $id = 101221007;
                break;
            case'黄山市   ':
                $id = 101221008;
                break;
            case'滁州    ':
                $id = 101221101;
                break;
            case'凤阳    ':
                $id = 101221102;
                break;
            case'明光    ':
                $id = 101221103;
                break;
            case'定远    ':
                $id = 101221104;
                break;
            case'全椒    ':
                $id = 101221105;
                break;
            case'来安    ':
                $id = 101221106;
                break;
            case'天长    ':
                $id = 101221107;
                break;
            case'淮北    ':
                $id = 101221201;
                break;
            case'濉溪    ':
                $id = 101221202;
                break;
            case'铜陵    ':
                $id = 101221301;
                break;
            case'宣城    ':
                $id = 101221401;
                break;
            case'泾县    ':
                $id = 101221402;
                break;
            case'旌德    ':
                $id = 101221403;
                break;
            case'宁国    ':
                $id = 101221404;
                break;
            case'绩溪    ':
                $id = 101221405;
                break;
            case'广德    ':
                $id = 101221406;
                break;
            case'郎溪    ':
                $id = 101221407;
                break;
            case'六安    ':
                $id = 101221501;
                break;
            case'霍邱    ':
                $id = 101221502;
                break;
            case'寿县    ':
                $id = 101221503;
                break;
            case'南溪    ':
                $id = 101221504;
                break;
            case'金寨    ':
                $id = 101221505;
                break;
            case'霍山    ':
                $id = 101221506;
                break;
            case'舒城    ':
                $id = 101221507;
                break;
            case'巢湖    ':
                $id = 101221601;
                break;
            case'庐江    ':
                $id = 101221602;
                break;
            case'无为    ':
                $id = 101221603;
                break;
            case'含山    ':
                $id = 101221604;
                break;
            case'和县    ':
                $id = 101221605;
                break;
            case'池州    ':
                $id = 101221701;
                break;
            case'东至    ':
                $id = 101221702;
                break;
            case'青阳    ':
                $id = 101221703;
                break;
            case'九华山   ':
                $id = 101221704;
                break;
            case'石台    ':
                $id = 101221705;
                break;

            case'福州    ':
                $id = 101230101;
                break;
            case'闽清    ':
                $id = 101230102;
                break;
            case'闽侯    ':
                $id = 101230103;
                break;
            case'罗源    ':
                $id = 101230104;
                break;
            case'连江    ':
                $id = 101230105;
                break;
            case'马祖    ':
                $id = 101230106;
                break;
            case'永泰    ':
                $id = 101230107;
                break;
            case'平潭    ':
                $id = 101230108;
                break;
            case'福州郊区  ':
                $id = 101230109;
                break;
            case'长乐    ':
                $id = 101230110;
                break;
            case'福清    ':
                $id = 101230111;
                break;
            case'平潭海峡大桥':
                $id = 101230112;
                break;
            case'厦门    ':
                $id = 101230201;
                break;
            case'同安    ':
                $id = 101230202;
                break;
            case'宁德    ':
                $id = 101230301;
                break;
            case'古田    ':
                $id = 101230302;
                break;
            case'霞浦    ':
                $id = 101230303;
                break;
            case'寿宁    ':
                $id = 101230304;
                break;
            case'周宁    ':
                $id = 101230305;
                break;
            case'福安    ':
                $id = 101230306;
                break;
            case'柘荣    ':
                $id = 101230307;
                break;
            case'福鼎    ':
                $id = 101230308;
                break;
            case'屏南    ':
                $id = 101230309;
                break;
            case'莆田    ':
                $id = 101230401;
                break;
            case'仙游    ':
                $id = 101230402;
                break;
            case'秀屿港   ':
                $id = 101230403;
                break;
            case'泉州    ':
                $id = 101230501;
                break;
            case'安溪    ':
                $id = 101230502;
                break;
            case'九仙山   ':
                $id = 101230503;
                break;
            case'永春    ':
                $id = 101230504;
                break;
            case'德化    ':
                $id = 101230505;
                break;
            case'南安    ':
                $id = 101230506;
                break;
            case'崇武    ':
                $id = 101230507;
                break;
            case'金山    ':
                $id = 101230508;
                break;
            case'晋江    ':
                $id = 101230509;
                break;
            case'漳州    ':
                $id = 101230601;
                break;
            case'长泰    ':
                $id = 101230602;
                break;
            case'南靖    ':
                $id = 101230603;
                break;
            case'平和    ':
                $id = 101230604;
                break;
            case'龙海    ':
                $id = 101230605;
                break;
            case'漳浦    ':
                $id = 101230606;
                break;
            case'诏安    ':
                $id = 101230607;
                break;
            case'东山    ':
                $id = 101230608;
                break;
            case'云霄    ':
                $id = 101230609;
                break;
            case'华安    ':
                $id = 101230610;
                break;
            case'龙岩    ':
                $id = 101230701;
                break;
            case'长汀    ':
                $id = 101230702;
                break;
            case'连城    ':
                $id = 101230703;
                break;
            case'武平    ':
                $id = 101230704;
                break;
            case'上杭    ':
                $id = 101230705;
                break;
            case'永定    ':
                $id = 101230706;
                break;
            case'漳平    ':
                $id = 101230707;
                break;
            case'三明    ':
                $id = 101230801;
                break;
            case'宁化    ':
                $id = 101230802;
                break;
            case'清流    ':
                $id = 101230803;
                break;
            case'泰宁    ':
                $id = 101230804;
                break;
            case'将乐    ':
                $id = 101230805;
                break;
            case'建宁    ':
                $id = 101230806;
                break;
            case'明溪    ':
                $id = 101230807;
                break;
            case'沙县    ':
                $id = 101230808;
                break;
            case'尤溪    ':
                $id = 101230809;
                break;
            case'永安    ':
                $id = 101230810;
                break;
            case'大田    ':
                $id = 101230811;
                break;
            case'南平    ':
                $id = 101230901;
                break;
            case'顺昌    ':
                $id = 101230902;
                break;
            case'光泽    ':
                $id = 101230903;
                break;
            case'邵武    ':
                $id = 101230904;
                break;
            case'武夷山   ':
                $id = 101230905;
                break;
            case'浦城    ':
                $id = 101230906;
                break;
            case'建阳    ':
                $id = 101230907;
                break;
            case'松溪    ':
                $id = 101230908;
                break;
            case'政和    ':
                $id = 101230909;
                break;
            case'建瓯    ':
                $id = 101230910;
                break;

            case'南昌    ':
                $id = 101240101;
                break;
            case'新建    ':
                $id = 101240102;
                break;
            case'南昌县   ':
                $id = 101240103;
                break;
            case'安义    ':
                $id = 101240104;
                break;
            case'进贤    ':
                $id = 101240105;
                break;
            case'莲塘    ':
                $id = 101240106;
                break;
            case'九江    ':
                $id = 101240201;
                break;
            case'瑞昌    ':
                $id = 101240202;
                break;
            case'庐山    ':
                $id = 101240203;
                break;
            case'武宁    ':
                $id = 101240204;
                break;
            case'德安    ':
                $id = 101240205;
                break;
            case'永修    ':
                $id = 101240206;
                break;
            case'湖口    ':
                $id = 101240207;
                break;
            case'彭泽    ':
                $id = 101240208;
                break;
            case'星子    ':
                $id = 101240209;
                break;
            case'都昌    ':
                $id = 101240210;
                break;
            case'棠荫    ':
                $id = 101240211;
                break;
            case'修水    ':
                $id = 101240212;
                break;
            case'上饶    ':
                $id = 101240301;
                break;
            case'鄱阳    ':
                $id = 101240302;
                break;
            case'婺源    ':
                $id = 101240303;
                break;
            case'康山    ':
                $id = 101240304;
                break;
            case'余干    ':
                $id = 101240305;
                break;
            case'万年    ':
                $id = 101240306;
                break;
            case'德兴    ':
                $id = 101240307;
                break;
            case'上饶县   ':
                $id = 101240308;
                break;
            case'弋阳    ':
                $id = 101240309;
                break;
            case'横峰    ':
                $id = 101240310;
                break;
            case'铅山    ':
                $id = 101240311;
                break;
            case'玉山    ':
                $id = 101240312;
                break;
            case'广丰    ':
                $id = 101240313;
                break;
            case'波阳    ':
                $id = 101240314;
                break;
            case'抚州    ':
                $id = 101240401;
                break;
            case'广昌    ':
                $id = 101240402;
                break;
            case'乐安    ':
                $id = 101240403;
                break;
            case'崇仁    ':
                $id = 101240404;
                break;
            case'金溪    ':
                $id = 101240405;
                break;
            case'资溪    ':
                $id = 101240406;
                break;
            case'宜黄    ':
                $id = 101240407;
                break;
            case'南城    ':
                $id = 101240408;
                break;
            case'南丰    ':
                $id = 101240409;
                break;
            case'黎川    ':
                $id = 101240410;
                break;
            case'东乡    ':
                $id = 101240411;
                break;
            case'宜春    ':
                $id = 101240501;
                break;
            case'铜鼓    ':
                $id = 101240502;
                break;
            case'宜丰    ':
                $id = 101240503;
                break;
            case'万载    ':
                $id = 101240504;
                break;
            case'上高    ':
                $id = 101240505;
                break;
            case'靖安    ':
                $id = 101240506;
                break;
            case'奉新    ':
                $id = 101240507;
                break;
            case'高安    ':
                $id = 101240508;
                break;
            case'樟树    ':
                $id = 101240509;
                break;
            case'丰城    ':
                $id = 101240510;
                break;
            case'吉安    ':
                $id = 101240601;
                break;
            case'吉安县   ':
                $id = 101240602;
                break;
            case'吉水    ':
                $id = 101240603;
                break;
            case'新干    ':
                $id = 101240604;
                break;
            case'峡江    ':
                $id = 101240605;
                break;
            case'永丰    ':
                $id = 101240606;
                break;
            case'永新    ':
                $id = 101240607;
                break;
            case'井冈山   ':
                $id = 101240608;
                break;
            case'万安    ':
                $id = 101240609;
                break;
            case'遂川    ':
                $id = 101240610;
                break;
            case'泰和    ':
                $id = 101240611;
                break;
            case'安福    ':
                $id = 101240612;
                break;
            case'宁冈    ':
                $id = 101240613;
                break;
            case'赣州    ':
                $id = 101240701;
                break;
            case'崇义    ':
                $id = 101240702;
                break;
            case'上犹    ':
                $id = 101240703;
                break;
            case'南康    ':
                $id = 101240704;
                break;
            case'大余    ':
                $id = 101240705;
                break;
            case'信丰    ':
                $id = 101240706;
                break;
            case'宁都    ':
                $id = 101240707;
                break;
            case'石城    ':
                $id = 101240708;
                break;
            case'瑞金    ':
                $id = 101240709;
                break;
            case'于都    ':
                $id = 101240710;
                break;
            case'会昌    ':
                $id = 101240711;
                break;
            case'安远    ':
                $id = 101240712;
                break;
            case'全南    ':
                $id = 101240713;
                break;
            case'龙南    ':
                $id = 101240714;
                break;
            case'定南    ':
                $id = 101240715;
                break;
            case'寻乌    ':
                $id = 101240716;
                break;
            case'兴国    ':
                $id = 101240717;
                break;
            case'景德镇   ':
                $id = 101240801;
                break;
            case'乐平    ':
                $id = 101240802;
                break;
            case'萍乡    ':
                $id = 101240901;
                break;
            case'莲花    ':
                $id = 101240902;
                break;
            case'新余    ':
                $id = 101241001;
                break;
            case'分宜    ':
                $id = 101241002;
                break;
            case'鹰潭    ':
                $id = 101241101;
                break;
            case'余江    ':
                $id = 101241102;
                break;
            case'贵溪    ':
                $id = 101241103;
                break;

            case'长沙    ':
                $id = 101250101;
                break;
            case'宁乡    ':
                $id = 101250102;
                break;
            case'浏阳    ':
                $id = 101250103;
                break;
            case'马坡岭   ':
                $id = 101250104;
                break;
            case'湘潭    ':
                $id = 101250201;
                break;
            case'韶山    ':
                $id = 101250202;
                break;
            case'湘乡    ':
                $id = 101250203;
                break;
            case'株洲    ':
                $id = 101250301;
                break;
            case'攸县    ':
                $id = 101250302;
                break;
            case'醴陵    ':
                $id = 101250303;
                break;
            case'株洲县   ':
                $id = 101250304;
                break;
            case'茶陵    ':
                $id = 101250305;
                break;
            case'炎陵    ':
                $id = 101250306;
                break;
            case'衡阳    ':
                $id = 101250401;
                break;
            case'衡山    ':
                $id = 101250402;
                break;
            case'衡东    ':
                $id = 101250403;
                break;
            case'祁东    ':
                $id = 101250404;
                break;
            case'衡阳县   ':
                $id = 101250405;
                break;
            case'常宁    ':
                $id = 101250406;
                break;
            case'衡南    ':
                $id = 101250407;
                break;
            case'耒阳    ':
                $id = 101250408;
                break;
            case'南岳    ':
                $id = 101250409;
                break;
            case'郴州    ':
                $id = 101250501;
                break;
            case'桂阳    ':
                $id = 101250502;
                break;
            case'嘉禾    ':
                $id = 101250503;
                break;
            case'宜章    ':
                $id = 101250504;
                break;
            case'临武    ':
                $id = 101250505;
                break;
            case'桥口    ':
                $id = 101250506;
                break;
            case'资兴    ':
                $id = 101250507;
                break;
            case'汝城    ':
                $id = 101250508;
                break;
            case'安仁    ':
                $id = 101250509;
                break;
            case'永兴    ':
                $id = 101250510;
                break;
            case'桂东    ':
                $id = 101250511;
                break;
            case'常德    ':
                $id = 101250601;
                break;
            case'安乡    ':
                $id = 101250602;
                break;
            case'桃源    ':
                $id = 101250603;
                break;
            case'汉寿    ':
                $id = 101250604;
                break;
            case'澧县    ':
                $id = 101250605;
                break;
            case'临澧    ':
                $id = 101250606;
                break;
            case'石门    ':
                $id = 101250607;
                break;
            case'益阳    ':
                $id = 101250700;
                break;
            case'赫山区   ':
                $id = 101250701;
                break;
            case'南县    ':
                $id = 101250702;
                break;
            case'桃江    ':
                $id = 101250703;
                break;
            case'安化    ':
                $id = 101250704;
                break;
            case'沅江    ':
                $id = 101250705;
                break;
            case'娄底    ':
                $id = 101250801;
                break;
            case'双峰    ':
                $id = 101250802;
                break;
            case'冷水江   ':
                $id = 101250803;
                break;
            case'冷水滩   ':
                $id = 101250804;
                break;
            case'新化    ':
                $id = 101250805;
                break;
            case'涟源    ':
                $id = 101250806;
                break;
            case'邵阳    ':
                $id = 101250901;
                break;
            case'隆回    ':
                $id = 101250902;
                break;
            case'洞口    ':
                $id = 101250903;
                break;
            case'新邵    ':
                $id = 101250904;
                break;
            case'邵东    ':
                $id = 101250905;
                break;
            case'绥宁    ':
                $id = 101250906;
                break;
            case'新宁    ':
                $id = 101250907;
                break;
            case'武冈    ':
                $id = 101250908;
                break;
            case'城步    ':
                $id = 101250909;
                break;
            case'邵阳县   ':
                $id = 101250910;
                break;
            case'岳阳    ':
                $id = 101251001;
                break;
            case'华容    ':
                $id = 101251002;
                break;
            case'湘阴    ':
                $id = 101251003;
                break;
            case'汨罗    ':
                $id = 101251004;
                break;
            case'平江    ':
                $id = 101251005;
                break;
            case'临湘    ':
                $id = 101251006;
                break;
            case'张家界   ':
                $id = 101251101;
                break;
            case'桑植    ':
                $id = 101251102;
                break;
            case'慈利    ':
                $id = 101251103;
                break;
            case'怀化    ':
                $id = 101251201;
                break;
            case'鹤城区   ':
                $id = 101251202;
                break;
            case'沅陵    ':
                $id = 101251203;
                break;
            case'辰溪    ':
                $id = 101251204;
                break;
            case'靖州    ':
                $id = 101251205;
                break;
            case'会同    ':
                $id = 101251206;
                break;
            case'通道    ':
                $id = 101251207;
                break;
            case'麻阳    ':
                $id = 101251208;
                break;
            case'新晃    ':
                $id = 101251209;
                break;
            case'芷江    ':
                $id = 101251210;
                break;
            case'溆浦    ':
                $id = 101251211;
                break;
            case'黔阳    ':
                $id = 101251301;
                break;
            case'洪江    ':
                $id = 101251302;
                break;
            case'永州    ':
                $id = 101251401;
                break;
            case'祁阳    ':
                $id = 101251402;
                break;
            case'东安    ':
                $id = 101251403;
                break;
            case'双牌    ':
                $id = 101251404;
                break;
            case'道县    ':
                $id = 101251405;
                break;
            case'宁远    ':
                $id = 101251406;
                break;
            case'江永    ':
                $id = 101251407;
                break;
            case'蓝山    ':
                $id = 101251408;
                break;
            case'新田    ':
                $id = 101251409;
                break;
            case'江华    ':
                $id = 101251410;
                break;
            case'吉首    ':
                $id = 101251501;
                break;
            case'保靖    ':
                $id = 101251502;
                break;
            case'永顺    ':
                $id = 101251503;
                break;
            case'古丈    ':
                $id = 101251504;
                break;
            case'凤凰    ':
                $id = 101251505;
                break;
            case'泸溪    ':
                $id = 101251506;
                break;
            case'龙山    ':
                $id = 101251507;
                break;
            case'花垣    ':
                $id = 101251508;
                break;

            case'贵阳    ':
                $id = 101260101;
                break;
            case'白云    ':
                $id = 101260102;
                break;
            case'花溪    ':
                $id = 101260103;
                break;
            case'乌当    ':
                $id = 101260104;
                break;
            case'息烽    ':
                $id = 101260105;
                break;
            case'开阳    ':
                $id = 101260106;
                break;
            case'修文    ':
                $id = 101260107;
                break;
            case'清镇    ':
                $id = 101260108;
                break;
            case'遵义    ':
                $id = 101260201;
                break;
            case'遵义县   ':
                $id = 101260202;
                break;
            case'仁怀    ':
                $id = 101260203;
                break;
            case'绥阳    ':
                $id = 101260204;
                break;
            case'湄潭    ':
                $id = 101260205;
                break;
            case'凤冈    ':
                $id = 101260206;
                break;
            case'桐梓    ':
                $id = 101260207;
                break;
            case'赤水    ':
                $id = 101260208;
                break;
            case'习水    ':
                $id = 101260209;
                break;
            case'道真    ':
                $id = 101260210;
                break;
            case'正安    ':
                $id = 101260211;
                break;
            case'务川    ':
                $id = 101260212;
                break;
            case'余庆    ':
                $id = 101260213;
                break;
            case'汇川    ':
                $id = 101260214;
                break;
            case'安顺    ':
                $id = 101260301;
                break;
            case'普定    ':
                $id = 101260302;
                break;
            case'镇宁    ':
                $id = 101260303;
                break;
            case'平坝    ':
                $id = 101260304;
                break;
            case'紫云    ':
                $id = 101260305;
                break;
            case'关岭    ':
                $id = 101260306;
                break;
            case'都匀    ':
                $id = 101260401;
                break;
            case'贵定    ':
                $id = 101260402;
                break;
            case'瓮安    ':
                $id = 101260403;
                break;
            case'长顺    ':
                $id = 101260404;
                break;
            case'福泉    ':
                $id = 101260405;
                break;
            case'惠水    ':
                $id = 101260406;
                break;
            case'龙里    ':
                $id = 101260407;
                break;
            case'罗甸    ':
                $id = 101260408;
                break;
            case'平塘    ':
                $id = 101260409;
                break;
            case'独山    ':
                $id = 101260410;
                break;
            case'三都    ':
                $id = 101260411;
                break;
            case'荔波    ':
                $id = 101260412;
                break;
            case'凯里    ':
                $id = 101260501;
                break;
            case'岑巩    ':
                $id = 101260502;
                break;
            case'施秉    ':
                $id = 101260503;
                break;
            case'镇远    ':
                $id = 101260504;
                break;
            case'黄平    ':
                $id = 101260505;
                break;
            case'黄平旧洲  ':
                $id = 101260506;
                break;
            case'麻江    ':
                $id = 101260507;
                break;
            case'丹寨    ':
                $id = 101260508;
                break;
            case'三穗    ':
                $id = 101260509;
                break;
            case'台江    ':
                $id = 101260510;
                break;
            case'剑河    ':
                $id = 101260511;
                break;
            case'雷山    ':
                $id = 101260512;
                break;
            case'黎平    ':
                $id = 101260513;
                break;
            case'天柱    ':
                $id = 101260514;
                break;
            case'锦屏    ':
                $id = 101260515;
                break;
            case'榕江    ':
                $id = 101260516;
                break;
            case'从江    ':
                $id = 101260517;
                break;
            case'炉山    ':
                $id = 101260518;
                break;
            case'铜仁    ':
                $id = 101260601;
                break;
            case'江口    ':
                $id = 101260602;
                break;
            case'玉屏    ':
                $id = 101260603;
                break;
            case'万山    ':
                $id = 101260604;
                break;
            case'思南    ':
                $id = 101260605;
                break;
            case'塘头    ':
                $id = 101260606;
                break;
            case'印江    ':
                $id = 101260607;
                break;
            case'石阡    ':
                $id = 101260608;
                break;
            case'沿河    ':
                $id = 101260609;
                break;
            case'德江    ':
                $id = 101260610;
                break;
            case'松桃    ':
                $id = 101260611;
                break;
            case'毕节    ':
                $id = 101260701;
                break;
            case'赫章    ':
                $id = 101260702;
                break;
            case'金沙    ':
                $id = 101260703;
                break;
            case'威宁    ':
                $id = 101260704;
                break;
            case'大方    ':
                $id = 101260705;
                break;
            case'纳雍    ':
                $id = 101260706;
                break;
            case'织金    ':
                $id = 101260707;
                break;
            case'六盘水   ':
                $id = 101260801;
                break;
            case'六枝    ':
                $id = 101260802;
                break;
            case'水城    ':
                $id = 101260803;
                break;
            case'盘县    ':
                $id = 101260804;
                break;
            case'黔西    ':
                $id = 101260901;
                break;
            case'晴隆    ':
                $id = 101260902;
                break;
            case'兴仁    ':
                $id = 101260903;
                break;
            case'贞丰    ':
                $id = 101260904;
                break;
            case'望谟    ':
                $id = 101260905;
                break;
            case'兴义    ':
                $id = 101260906;
                break;
            case'安龙    ':
                $id = 101260907;
                break;
            case'册亨    ':
                $id = 101260908;
                break;
            case'普安    ':
                $id = 101260909;
                break;

            case'成都    ':
                $id = 101270101;
                break;
            case'龙泉驿   ':
                $id = 101270102;
                break;
            case'新都    ':
                $id = 101270103;
                break;
            case'温江    ':
                $id = 101270104;
                break;
            case'金堂    ':
                $id = 101270105;
                break;
            case'双流    ':
                $id = 101270106;
                break;
            case'郫县    ':
                $id = 101270107;
                break;
            case'大邑    ':
                $id = 101270108;
                break;
            case'蒲江    ':
                $id = 101270109;
                break;
            case'新津    ':
                $id = 101270110;
                break;
            case'都江堰   ':
                $id = 101270111;
                break;
            case'彭州    ':
                $id = 101270112;
                break;
            case'邛崃    ':
                $id = 101270113;
                break;
            case'崇州    ':
                $id = 101270114;
                break;
            case'崇庆    ':
                $id = 101270115;
                break;
            case'彭县    ':
                $id = 101270116;
                break;
            case'攀枝花   ':
                $id = 101270201;
                break;
            case'仁和    ':
                $id = 101270202;
                break;
            case'米易    ':
                $id = 101270203;
                break;
            case'盐边    ':
                $id = 101270204;
                break;
            case'自贡    ':
                $id = 101270301;
                break;
            case'富顺    ':
                $id = 101270302;
                break;
            case'荣县    ':
                $id = 101270303;
                break;
            case'绵阳    ':
                $id = 101270401;
                break;
            case'三台    ':
                $id = 101270402;
                break;
            case'盐亭    ':
                $id = 101270403;
                break;
            case'安县    ':
                $id = 101270404;
                break;
            case'梓潼    ':
                $id = 101270405;
                break;
            case'北川    ':
                $id = 101270406;
                break;
            case'平武    ':
                $id = 101270407;
                break;
            case'江油    ':
                $id = 101270408;
                break;
            case'南充    ':
                $id = 101270501;
                break;
            case'南部    ':
                $id = 101270502;
                break;
            case'营山    ':
                $id = 101270503;
                break;
            case'蓬安    ':
                $id = 101270504;
                break;
            case'仪陇    ':
                $id = 101270505;
                break;
            case'西充    ':
                $id = 101270506;
                break;
            case'阆中    ':
                $id = 101270507;
                break;
            case'达州    ':
                $id = 101270601;
                break;
            case'宣汉    ':
                $id = 101270602;
                break;
            case'开江    ':
                $id = 101270603;
                break;
            case'大竹    ':
                $id = 101270604;
                break;
            case'渠县    ':
                $id = 101270605;
                break;
            case'万源    ':
                $id = 101270606;
                break;
            case'达川    ':
                $id = 101270607;
                break;
            case'遂宁    ':
                $id = 101270701;
                break;
            case'蓬溪    ':
                $id = 101270702;
                break;
            case'射洪    ':
                $id = 101270703;
                break;
            case'广安    ':
                $id = 101270801;
                break;
            case'岳池    ':
                $id = 101270802;
                break;
            case'武胜    ':
                $id = 101270803;
                break;
            case'邻水    ':
                $id = 101270804;
                break;
            case'华蓥山   ':
                $id = 101270805;
                break;
            case'巴中    ':
                $id = 101270901;
                break;
            case'通江    ':
                $id = 101270902;
                break;
            case'南江    ':
                $id = 101270903;
                break;
            case'平昌    ':
                $id = 101270904;
                break;
            case'泸州    ':
                $id = 101271001;
                break;
            case'泸县    ':
                $id = 101271003;
                break;
            case'合江    ':
                $id = 101271004;
                break;
            case'叙永    ':
                $id = 101271005;
                break;
            case'古蔺    ':
                $id = 101271006;
                break;
            case'纳溪    ':
                $id = 101271007;
                break;
            case'宜宾    ':
                $id = 101271101;
                break;
            case'宜宾农试站 ':
                $id = 101271102;
                break;
            case'宜宾县   ':
                $id = 101271103;
                break;
            case'南溪    ':
                $id = 101271104;
                break;
            case'江安    ':
                $id = 101271105;
                break;
            case'长宁    ':
                $id = 101271106;
                break;
            case'高县    ':
                $id = 101271107;
                break;
            case'珙县    ':
                $id = 101271108;
                break;
            case'筠连    ':
                $id = 101271109;
                break;
            case'兴文    ':
                $id = 101271110;
                break;
            case'屏山    ':
                $id = 101271111;
                break;
            case'内江    ':
                $id = 101271201;
                break;
            case'东兴    ':
                $id = 101271202;
                break;
            case'威远    ':
                $id = 101271203;
                break;
            case'资中    ':
                $id = 101271204;
                break;
            case'隆昌    ':
                $id = 101271205;
                break;
            case'资阳    ':
                $id = 101271301;
                break;
            case'安岳    ':
                $id = 101271302;
                break;
            case'乐至    ':
                $id = 101271303;
                break;
            case'简阳    ':
                $id = 101271304;
                break;
            case'乐山    ':
                $id = 101271401;
                break;
            case'犍为    ':
                $id = 101271402;
                break;
            case'井研    ':
                $id = 101271403;
                break;
            case'夹江    ':
                $id = 101271404;
                break;
            case'沐川    ':
                $id = 101271405;
                break;
            case'峨边    ':
                $id = 101271406;
                break;
            case'马边    ':
                $id = 101271407;
                break;
            case'峨眉    ':
                $id = 101271408;
                break;
            case'峨眉山   ':
                $id = 101271409;
                break;
            case'眉山    ':
                $id = 101271501;
                break;
            case'仁寿    ':
                $id = 101271502;
                break;
            case'彭山    ':
                $id = 101271503;
                break;
            case'洪雅    ':
                $id = 101271504;
                break;
            case'丹棱    ':
                $id = 101271505;
                break;
            case'青神    ':
                $id = 101271506;
                break;
            case'凉山    ':
                $id = 101271601;
                break;
            case'木里    ':
                $id = 101271603;
                break;
            case'盐源    ':
                $id = 101271604;
                break;
            case'德昌    ':
                $id = 101271605;
                break;
            case'会理    ':
                $id = 101271606;
                break;
            case'会东    ':
                $id = 101271607;
                break;
            case'宁南    ':
                $id = 101271608;
                break;
            case'普格    ':
                $id = 101271609;
                break;
            case'西昌    ':
                $id = 101271610;
                break;
            case'金阳    ':
                $id = 101271611;
                break;
            case'昭觉    ':
                $id = 101271612;
                break;
            case'喜德    ':
                $id = 101271613;
                break;
            case'冕宁    ':
                $id = 101271614;
                break;
            case'越西    ':
                $id = 101271615;
                break;
            case'甘洛    ':
                $id = 101271616;
                break;
            case'雷波    ':
                $id = 101271617;
                break;
            case'美姑    ':
                $id = 101271618;
                break;
            case'布拖    ':
                $id = 101271619;
                break;
            case'雅安    ':
                $id = 101271701;
                break;
            case'名山    ':
                $id = 101271702;
                break;
            case'荣经    ':
                $id = 101271703;
                break;
            case'汉源    ':
                $id = 101271704;
                break;
            case'石棉    ':
                $id = 101271705;
                break;
            case'天全    ':
                $id = 101271706;
                break;
            case'芦山    ':
                $id = 101271707;
                break;
            case'宝兴    ':
                $id = 101271708;
                break;
            case'甘孜    ':
                $id = 101271801;
                break;
            case'康定    ':
                $id = 101271802;
                break;
            case'泸定    ':
                $id = 101271803;
                break;
            case'丹巴    ':
                $id = 101271804;
                break;
            case'九龙    ':
                $id = 101271805;
                break;
            case'雅江    ':
                $id = 101271806;
                break;
            case'道孚    ':
                $id = 101271807;
                break;
            case'炉霍    ':
                $id = 101271808;
                break;
            case'新龙    ':
                $id = 101271809;
                break;
            case'德格    ':
                $id = 101271810;
                break;
            case'白玉    ':
                $id = 101271811;
                break;
            case'石渠    ':
                $id = 101271812;
                break;
            case'色达    ':
                $id = 101271813;
                break;
            case'理塘    ':
                $id = 101271814;
                break;
            case'巴塘    ':
                $id = 101271815;
                break;
            case'乡城    ':
                $id = 101271816;
                break;
            case'稻城    ':
                $id = 101271817;
                break;
            case'得荣    ':
                $id = 101271818;
                break;
            case'阿坝    ':
                $id = 101271901;
                break;
            case'汶川    ':
                $id = 101271902;
                break;
            case'理县    ':
                $id = 101271903;
                break;
            case'茂县    ':
                $id = 101271904;
                break;
            case'松潘    ':
                $id = 101271905;
                break;
            case'九寨沟   ':
                $id = 101271906;
                break;
            case'金川    ':
                $id = 101271907;
                break;
            case'小金    ':
                $id = 101271908;
                break;
            case'黑水    ':
                $id = 101271909;
                break;
            case'马尔康   ':
                $id = 101271910;
                break;
            case'壤塘    ':
                $id = 101271911;
                break;
            case'若尔盖   ':
                $id = 101271912;
                break;
            case'红原    ':
                $id = 101271913;
                break;
            case'南坪    ':
                $id = 101271914;
                break;
            case'德阳    ':
                $id = 101272001;
                break;
            case'中江    ':
                $id = 101272002;
                break;
            case'广汉    ':
                $id = 101272003;
                break;
            case'什邡    ':
                $id = 101272004;
                break;
            case'绵竹    ':
                $id = 101272005;
                break;
            case'罗江    ':
                $id = 101272006;
                break;
            case'广元    ':
                $id = 101272101;
                break;
            case'旺苍    ':
                $id = 101272102;
                break;
            case'青川    ':
                $id = 101272103;
                break;
            case'剑阁    ':
                $id = 101272104;
                break;
            case'苍溪    ':
                $id = 101272105;
                break;

            case'广州    ':
                $id = 101280101;
                break;
            case'番禺    ':
                $id = 101280102;
                break;
            case'从化    ':
                $id = 101280103;
                break;
            case'增城    ':
                $id = 101280104;
                break;
            case'花都    ':
                $id = 101280105;
                break;
            case'天河    ':
                $id = 101280106;
                break;
            case'韶关    ':
                $id = 101280201;
                break;
            case'乳源    ':
                $id = 101280202;
                break;
            case'始兴    ':
                $id = 101280203;
                break;
            case'翁源    ':
                $id = 101280204;
                break;
            case'乐昌    ':
                $id = 101280205;
                break;
            case'仁化    ':
                $id = 101280206;
                break;
            case'南雄    ':
                $id = 101280207;
                break;
            case'新丰    ':
                $id = 101280208;
                break;
            case'曲江    ':
                $id = 101280209;
                break;
            case'惠州    ':
                $id = 101280301;
                break;
            case'博罗    ':
                $id = 101280302;
                break;
            case'惠阳    ':
                $id = 101280303;
                break;
            case'惠东    ':
                $id = 101280304;
                break;
            case'龙门    ':
                $id = 101280305;
                break;
            case'梅州    ':
                $id = 101280401;
                break;
            case'兴宁    ':
                $id = 101280402;
                break;
            case'蕉岭    ':
                $id = 101280403;
                break;
            case'大埔    ':
                $id = 101280404;
                break;
            case'丰顺    ':
                $id = 101280406;
                break;
            case'平远    ':
                $id = 101280407;
                break;
            case'五华    ':
                $id = 101280408;
                break;
            case'梅县    ':
                $id = 101280409;
                break;
            case'汕头    ':
                $id = 101280501;
                break;
            case'潮阳    ':
                $id = 101280502;
                break;
            case'澄海    ':
                $id = 101280503;
                break;
            case'南澳    ':
                $id = 101280504;
                break;
            case'云澳    ':
                $id = 101280505;
                break;
            case'南澎岛   ':
                $id = 101280506;
                break;
            case'深圳    ':
                $id = 101280601;
                break;
            case'珠海    ':
                $id = 101280701;
                break;
            case'斗门    ':
                $id = 101280702;
                break;
            case'黄茅洲   ':
                $id = 101280703;
                break;
            case'佛山    ':
                $id = 101280800;
                break;
            case'顺德    ':
                $id = 101280801;
                break;
            case'三水    ':
                $id = 101280802;
                break;
            case'南海    ':
                $id = 101280803;
                break;
            case'肇庆    ':
                $id = 101280901;
                break;
            case'广宁    ':
                $id = 101280902;
                break;
            case'四会    ':
                $id = 101280903;
                break;
            case'德庆    ':
                $id = 101280905;
                break;
            case'怀集    ':
                $id = 101280906;
                break;
            case'封开    ':
                $id = 101280907;
                break;
            case'高要    ':
                $id = 101280908;
                break;
            case'湛江    ':
                $id = 101281001;
                break;
            case'吴川    ':
                $id = 101281002;
                break;
            case'雷州    ':
                $id = 101281003;
                break;
            case'徐闻    ':
                $id = 101281004;
                break;
            case'廉江    ':
                $id = 101281005;
                break;
            case'硇洲    ':
                $id = 101281006;
                break;
            case'遂溪    ':
                $id = 101281007;
                break;
            case'江门    ':
                $id = 101281101;
                break;
            case'开平    ':
                $id = 101281103;
                break;
            case'新会    ':
                $id = 101281104;
                break;
            case'恩平    ':
                $id = 101281105;
                break;
            case'台山    ':
                $id = 101281106;
                break;
            case'上川岛   ':
                $id = 101281107;
                break;
            case'鹤山    ':
                $id = 101281108;
                break;
            case'河源    ':
                $id = 101281201;
                break;
            case'紫金    ':
                $id = 101281202;
                break;
            case'连平    ':
                $id = 101281203;
                break;
            case'和平    ':
                $id = 101281204;
                break;
            case'龙川    ':
                $id = 101281205;
                break;
            case'清远    ':
                $id = 101281301;
                break;
            case'连南    ':
                $id = 101281302;
                break;
            case'连州    ':
                $id = 101281303;
                break;
            case'连山    ':
                $id = 101281304;
                break;
            case'阳山    ':
                $id = 101281305;
                break;
            case'佛冈    ':
                $id = 101281306;
                break;
            case'英德    ':
                $id = 101281307;
                break;
            case'云浮    ':
                $id = 101281401;
                break;
            case'罗定    ':
                $id = 101281402;
                break;
            case'新兴    ':
                $id = 101281403;
                break;
            case'郁南    ':
                $id = 101281404;
                break;
            case'潮州    ':
                $id = 101281501;
                break;
            case'饶平    ':
                $id = 101281502;
                break;
            case'东莞    ':
                $id = 101281601;
                break;
            case'中山    ':
                $id = 101281701;
                break;
            case'阳江    ':
                $id = 101281801;
                break;
            case'阳春    ':
                $id = 101281802;
                break;
            case'揭阳    ':
                $id = 101281901;
                break;
            case'揭西    ':
                $id = 101281902;
                break;
            case'普宁    ':
                $id = 101281903;
                break;
            case'惠来    ':
                $id = 101281904;
                break;
            case'茂名    ':
                $id = 101282001;
                break;
            case'高州    ':
                $id = 101282002;
                break;
            case'化州    ':
                $id = 101282003;
                break;
            case'电白    ':
                $id = 101282004;
                break;
            case'信宜    ':
                $id = 101282005;
                break;
            case'汕尾    ':
                $id = 101282101;
                break;
            case'海丰    ':
                $id = 101282102;
                break;
            case'陆丰    ':
                $id = 101282103;
                break;
            case'遮浪    ':
                $id = 101282104;
                break;
            case'东沙岛   ':
                $id = 101282105;
                break;

            case'昆明    ':
                $id = 101290101;
                break;
            case'昆明农试站 ':
                $id = 101290102;
                break;
            case'东川    ':
                $id = 101290103;
                break;
            case'寻甸    ':
                $id = 101290104;
                break;
            case'晋宁    ':
                $id = 101290105;
                break;
            case'宜良    ':
                $id = 101290106;
                break;
            case'石林    ':
                $id = 101290107;
                break;
            case'呈贡    ':
                $id = 101290108;
                break;
            case'富民    ':
                $id = 101290109;
                break;
            case'嵩明    ':
                $id = 101290110;
                break;
            case'禄劝    ':
                $id = 101290111;
                break;
            case'安宁    ':
                $id = 101290112;
                break;
            case'太华山   ':
                $id = 101290113;
                break;
            case'河口    ':
                $id = 101290114;
                break;
            case'大理    ':
                $id = 101290201;
                break;
            case'云龙    ':
                $id = 101290202;
                break;
            case'漾鼻    ':
                $id = 101290203;
                break;
            case'永平    ':
                $id = 101290204;
                break;
            case'宾川    ':
                $id = 101290205;
                break;
            case'弥渡    ':
                $id = 101290206;
                break;
            case'祥云    ':
                $id = 101290207;
                break;
            case'魏山    ':
                $id = 101290208;
                break;
            case'剑川    ':
                $id = 101290209;
                break;
            case'洱源    ':
                $id = 101290210;
                break;
            case'鹤庆    ':
                $id = 101290211;
                break;
            case'南涧    ':
                $id = 101290212;
                break;
            case'红河    ':
                $id = 101290301;
                break;
            case'石屏    ':
                $id = 101290302;
                break;
            case'建水    ':
                $id = 101290303;
                break;
            case'弥勒    ':
                $id = 101290304;
                break;
            case'元阳    ':
                $id = 101290305;
                break;
            case'绿春    ':
                $id = 101290306;
                break;
            case'开远    ':
                $id = 101290307;
                break;
            case'个旧    ':
                $id = 101290308;
                break;
            case'蒙自    ':
                $id = 101290309;
                break;
            case'屏边    ':
                $id = 101290310;
                break;
            case'泸西    ':
                $id = 101290311;
                break;
            case'金平    ':
                $id = 101290312;
                break;
            case'曲靖    ':
                $id = 101290401;
                break;
            case'沾益    ':
                $id = 101290402;
                break;
            case'陆良    ':
                $id = 101290403;
                break;
            case'富源    ':
                $id = 101290404;
                break;
            case'马龙    ':
                $id = 101290405;
                break;
            case'师宗    ':
                $id = 101290406;
                break;
            case'罗平    ':
                $id = 101290407;
                break;
            case'会泽    ':
                $id = 101290408;
                break;
            case'宣威    ':
                $id = 101290409;
                break;
            case'保山    ':
                $id = 101290501;
                break;
            case'富宁    ':
                $id = 101290502;
                break;
            case'龙陵    ':
                $id = 101290503;
                break;
            case'施甸    ':
                $id = 101290504;
                break;
            case'昌宁    ':
                $id = 101290505;
                break;
            case'腾冲    ':
                $id = 101290506;
                break;
            case'文山    ':
                $id = 101290601;
                break;
            case'西畴    ':
                $id = 101290602;
                break;
            case'马关    ':
                $id = 101290603;
                break;
            case'麻栗坡   ':
                $id = 101290604;
                break;
            case'砚山    ':
                $id = 101290605;
                break;
            case'邱北    ':
                $id = 101290606;
                break;
            case'广南    ':
                $id = 101290607;
                break;
            case'玉溪    ':
                $id = 101290701;
                break;
            case'澄江    ':
                $id = 101290702;
                break;
            case'江川    ':
                $id = 101290703;
                break;
            case'通海    ':
                $id = 101290704;
                break;
            case'华宁    ':
                $id = 101290705;
                break;
            case'新平    ':
                $id = 101290706;
                break;
            case'易门    ':
                $id = 101290707;
                break;
            case'峨山    ':
                $id = 101290708;
                break;
            case'元江    ':
                $id = 101290709;
                break;
            case'楚雄    ':
                $id = 101290801;
                break;
            case'大姚    ':
                $id = 101290802;
                break;
            case'元谋    ':
                $id = 101290803;
                break;
            case'姚安    ':
                $id = 101290804;
                break;
            case'牟定    ':
                $id = 101290805;
                break;
            case'南华    ':
                $id = 101290806;
                break;
            case'武定    ':
                $id = 101290807;
                break;
            case'禄丰    ':
                $id = 101290808;
                break;
            case'双柏    ':
                $id = 101290809;
                break;
            case'永仁    ':
                $id = 101290810;
                break;
            case'普洱    ':
                $id = 101290901;
                break;
            case'景谷    ':
                $id = 101290902;
                break;
            case'景东    ':
                $id = 101290903;
                break;
            case'澜沧    ':
                $id = 101290904;
                break;
            case'普洱    ':
                $id = 101290905;
                break;
            case'墨江    ':
                $id = 101290906;
                break;
            case'江城    ':
                $id = 101290907;
                break;
            case'孟连    ':
                $id = 101290908;
                break;
            case'西盟    ':
                $id = 101290909;
                break;
            case'镇源    ':
                $id = 101290910;
                break;
            case'镇沅    ':
                $id = 101290911;
                break;
            case'宁洱    ':
                $id = 101290912;
                break;
            case'昭通    ':
                $id = 101291001;
                break;
            case'鲁甸    ':
                $id = 101291002;
                break;
            case'彝良    ':
                $id = 101291003;
                break;
            case'镇雄    ':
                $id = 101291004;
                break;
            case'威信    ':
                $id = 101291005;
                break;
            case'巧家    ':
                $id = 101291006;
                break;
            case'绥江    ':
                $id = 101291007;
                break;
            case'永善    ':
                $id = 101291008;
                break;
            case'盐津    ':
                $id = 101291009;
                break;
            case'大关    ':
                $id = 101291010;
                break;
            case'临沧    ':
                $id = 101291101;
                break;
            case'沧源    ':
                $id = 101291102;
                break;
            case'耿马    ':
                $id = 101291103;
                break;
            case'双江    ':
                $id = 101291104;
                break;
            case'凤庆    ':
                $id = 101291105;
                break;
            case'永德    ':
                $id = 101291106;
                break;
            case'云县    ':
                $id = 101291107;
                break;
            case'镇康    ':
                $id = 101291108;
                break;
            case'怒江    ':
                $id = 101291201;
                break;
            case'福贡    ':
                $id = 101291203;
                break;
            case'兰坪    ':
                $id = 101291204;
                break;
            case'泸水    ':
                $id = 101291205;
                break;
            case'六库    ':
                $id = 101291206;
                break;
            case'贡山    ':
                $id = 101291207;
                break;
            case'香格里拉  ':
                $id = 101291301;
                break;
            case'德钦    ':
                $id = 101291302;
                break;
            case'维西    ':
                $id = 101291303;
                break;
            case'中甸    ':
                $id = 101291304;
                break;
            case'丽江    ':
                $id = 101291401;
                break;
            case'永胜    ':
                $id = 101291402;
                break;
            case'华坪    ':
                $id = 101291403;
                break;
            case'宁蒗    ':
                $id = 101291404;
                break;
            case'德宏    ':
                $id = 101291501;
                break;
            case'潞江坝   ':
                $id = 101291502;
                break;
            case'陇川    ':
                $id = 101291503;
                break;
            case'盈江    ':
                $id = 101291504;
                break;
            case'畹町镇   ':
                $id = 101291505;
                break;
            case'瑞丽    ':
                $id = 101291506;
                break;
            case'梁河    ':
                $id = 101291507;
                break;
            case'潞西    ':
                $id = 101291508;
                break;
            case'景洪    ':
                $id = 101291601;
                break;
            case'大勐龙   ':
                $id = 101291602;
                break;
            case'勐海    ':
                $id = 101291603;
                break;
            case'景洪电站  ':
                $id = 101291604;
                break;
            case'勐腊    ':
                $id = 101291605;
                break;

            case'南宁    ':
                $id = 101300101;
                break;
            case'南宁城区  ':
                $id = 101300102;
                break;
            case'邕宁    ':
                $id = 101300103;
                break;
            case'横县    ':
                $id = 101300104;
                break;
            case'隆安    ':
                $id = 101300105;
                break;
            case'马山    ':
                $id = 101300106;
                break;
            case'上林    ':
                $id = 101300107;
                break;
            case'武鸣    ':
                $id = 101300108;
                break;
            case'宾阳    ':
                $id = 101300109;
                break;
            case'硕龙    ':
                $id = 101300110;
                break;
            case'崇左    ':
                $id = 101300201;
                break;
            case'天等    ':
                $id = 101300202;
                break;
            case'龙州    ':
                $id = 101300203;
                break;
            case'凭祥    ':
                $id = 101300204;
                break;
            case'大新    ':
                $id = 101300205;
                break;
            case'扶绥    ':
                $id = 101300206;
                break;
            case'宁明    ':
                $id = 101300207;
                break;
            case'海渊    ':
                $id = 101300208;
                break;
            case'柳州    ':
                $id = 101300301;
                break;
            case'柳城    ':
                $id = 101300302;
                break;
            case'沙塘    ':
                $id = 101300303;
                break;
            case'鹿寨    ':
                $id = 101300304;
                break;
            case'柳江    ':
                $id = 101300305;
                break;
            case'融安    ':
                $id = 101300306;
                break;
            case'融水    ':
                $id = 101300307;
                break;
            case'三江    ':
                $id = 101300308;
                break;
            case'来宾    ':
                $id = 101300401;
                break;
            case'忻城    ':
                $id = 101300402;
                break;
            case'金秀    ':
                $id = 101300403;
                break;
            case'象州    ':
                $id = 101300404;
                break;
            case'武宣    ':
                $id = 101300405;
                break;
            case'桂林    ':
                $id = 101300501;
                break;
            case'桂林农试站 ':
                $id = 101300502;
                break;
            case'龙胜    ':
                $id = 101300503;
                break;
            case'永福    ':
                $id = 101300504;
                break;
            case'临桂    ':
                $id = 101300505;
                break;
            case'兴安    ':
                $id = 101300506;
                break;
            case'灵川    ':
                $id = 101300507;
                break;
            case'全州    ':
                $id = 101300508;
                break;
            case'灌阳    ':
                $id = 101300509;
                break;
            case'阳朔    ':
                $id = 101300510;
                break;
            case'恭城    ':
                $id = 101300511;
                break;
            case'平乐    ':
                $id = 101300512;
                break;
            case'荔浦    ':
                $id = 101300513;
                break;
            case'资源    ':
                $id = 101300514;
                break;
            case'梧州    ':
                $id = 101300601;
                break;
            case'藤县    ':
                $id = 101300602;
                break;
            case'太平    ':
                $id = 101300603;
                break;
            case'苍梧    ':
                $id = 101300604;
                break;
            case'蒙山    ':
                $id = 101300605;
                break;
            case'岑溪    ':
                $id = 101300606;
                break;
            case'贺州    ':
                $id = 101300701;
                break;
            case'昭平    ':
                $id = 101300702;
                break;
            case'富川    ':
                $id = 101300703;
                break;
            case'钟山    ':
                $id = 101300704;
                break;
            case'信都    ':
                $id = 101300705;
                break;
            case'贵港    ':
                $id = 101300801;
                break;
            case'桂平    ':
                $id = 101300802;
                break;
            case'平南    ':
                $id = 101300803;
                break;
            case'玉林    ':
                $id = 101300901;
                break;
            case'博白    ':
                $id = 101300902;
                break;
            case'北流    ':
                $id = 101300903;
                break;
            case'容县    ':
                $id = 101300904;
                break;
            case'陆川    ':
                $id = 101300905;
                break;
            case'百色    ':
                $id = 101301001;
                break;
            case'那坡    ':
                $id = 101301002;
                break;
            case'田阳    ':
                $id = 101301003;
                break;
            case'德保    ':
                $id = 101301004;
                break;
            case'靖西    ':
                $id = 101301005;
                break;
            case'田东    ':
                $id = 101301006;
                break;
            case'平果    ':
                $id = 101301007;
                break;
            case'隆林    ':
                $id = 101301008;
                break;
            case'西林    ':
                $id = 101301009;
                break;
            case'乐业    ':
                $id = 101301010;
                break;
            case'凌云    ':
                $id = 101301011;
                break;
            case'田林    ':
                $id = 101301012;
                break;
            case'钦州    ':
                $id = 101301101;
                break;
            case'浦北    ':
                $id = 101301102;
                break;
            case'灵山    ':
                $id = 101301103;
                break;
            case'河池    ':
                $id = 101301201;
                break;
            case'天峨    ':
                $id = 101301202;
                break;
            case'东兰    ':
                $id = 101301203;
                break;
            case'巴马    ':
                $id = 101301204;
                break;
            case'环江    ':
                $id = 101301205;
                break;
            case'罗城    ':
                $id = 101301206;
                break;
            case'宜州    ':
                $id = 101301207;
                break;
            case'凤山    ':
                $id = 101301208;
                break;
            case'南丹    ':
                $id = 101301209;
                break;
            case'都安    ':
                $id = 101301210;
                break;
            case'北海    ':
                $id = 101301301;
                break;
            case'合浦    ':
                $id = 101301302;
                break;
            case'涠洲岛   ':
                $id = 101301303;
                break;
            case'防城港   ':
                $id = 101301401;
                break;
            case'上思    ':
                $id = 101301402;
                break;
            case'东兴    ':
                $id = 101301403;
                break;
            case'板栏    ':
                $id = 101301404;
                break;
            case'防城    ':
                $id = 101301405;
                break;

            case'海口    ':
                $id = 101310101;
                break;
            case'琼山    ':
                $id = 101310102;
                break;
            case'三亚    ':
                $id = 101310201;
                break;
            case'东方    ':
                $id = 101310202;
                break;
            case'临高    ':
                $id = 101310203;
                break;
            case'澄迈    ':
                $id = 101310204;
                break;
            case'儋州    ':
                $id = 101310205;
                break;
            case'昌江    ':
                $id = 101310206;
                break;
            case'白沙    ':
                $id = 101310207;
                break;
            case'琼中    ':
                $id = 101310208;
                break;
            case'定安    ':
                $id = 101310209;
                break;
            case'屯昌    ':
                $id = 101310210;
                break;
            case'琼海    ':
                $id = 101310211;
                break;
            case'文昌    ':
                $id = 101310212;
                break;
            case'清兰    ':
                $id = 101310213;
                break;
            case'保亭    ':
                $id = 101310214;
                break;
            case'万宁    ':
                $id = 101310215;
                break;
            case'陵水    ':
                $id = 101310216;
                break;
            case'西沙    ':
                $id = 101310217;
                break;
            case'珊瑚岛   ':
                $id = 101310218;
                break;
            case'永署礁   ':
                $id = 101310219;
                break;
            case'南沙岛   ':
                $id = 101310220;
                break;
            case'乐东    ':
                $id = 101310221;
                break;
            case'五指山   ':
                $id = 101310222;
                break;
            case'通什    ':
                $id = 101310223;
                break;

            case'香港    ':
                $id = 101320101;
                break;
            case'九龙    ':
                $id = 101320102;
                break;
            case'新界    ':
                $id = 101320103;
                break;
            case'中环    ':
                $id = 101320104;
                break;
            case'铜锣湾   ':
                $id = 101320105;
                break;

            case'澳门    ':
                $id = 101330101;
                break;

            case'台北县   ':
                $id = 101340101;
                break;
            case'台北市   ':
                $id = 101340102;
                break;
            case'高雄    ':
                $id = 101340201;
                break;
            case'东港    ':
                $id = 101340202;
                break;
            case'大武    ':
                $id = 101340203;
                break;
            case'恒春    ':
                $id = 101340204;
                break;
            case'兰屿    ':
                $id = 101340205;
                break;
            case'台南    ':
                $id = 101340301;
                break;
            case'台中    ':
                $id = 101340401;
                break;
            case'桃园    ':
                $id = 101340501;
                break;
            case'新竹县   ':
                $id = 101340601;
                break;
            case'新竹市   ':
                $id = 101340602;
                break;
            case'公馆    ':
                $id = 101340603;
                break;
            case'宜兰    ':
                $id = 101340701;
                break;
            case'马公    ':
                $id = 101340801;
                break;
            case'东吉屿   ':
                $id = 101340802;
                break;
            case'嘉义    ':
                $id = 101340901;
                break;
            case'阿里山   ':
                $id = 101340902;
                break;
            case'玉山    ':
                $id = 101340903;
                break;
            case'新港    ':
                $id = 101340904;
                break;
            default:
                $id = 101010100;
                break;
        }
        return $id;

    }

    function __call($name, $args)
    {
        showErrorpage('500', 'Call Error Method ' . $name . ' In Class ' . __CLASS__);
    }

    function init($name)
    {
        $id = self::weatherId($name);
        $url = "http://m.weather.com.cn/atad/{$id}.html";
        $ret = file_get_contents($url);
        $obj = json_decode($ret);
        // var_dump($obj);
        $data['city']=$obj->weatherinfo->city;
        $data['date_y']=$obj->weatherinfo->date_y;
        $data['date']=$obj->weatherinfo->date;
        $data['week']=$obj->weatherinfo->week;
        $data['temp1']=$obj->weatherinfo->temp1;
        $data['temp2']=$obj->weatherinfo->temp2;
        $data['temp3']=$obj->weatherinfo->temp3;
        $data['temp4']=$obj->weatherinfo->temp4;
        echo json_encode($data);
        
    }
}
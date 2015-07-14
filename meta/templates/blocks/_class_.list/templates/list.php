<?php
/**
 * Шаблон для блока фильтр Posts
 */
?>
<div class="panel panel-default">
    <div class="panel-heading">Фильтр</div>
    <div class="panel-body">
    <form action="<:websubdir:>" method="GET" class="filter">
    <table class="table">
    <tr id="filterheadercont">
        <th>Поле</td>
        <th>Сравнение</th>
        <th>Значение</th>
        <th>Связка</th>
    </tr>
    <? foreach($this->arFilter as $key => $filter): ?>
        <?php
        if( !in_array($key, array('p','sort')) ): 
            if(is_array($filter)){
                list($op,$val) = each($filter);
                if( is_array($val) && empty($val[0]) ){
                    if( isset($rels[$key]) ){
                        $key = $key.'.'.$op;
                        list($op,$val) = each($val);
                    }
                } elseif($op == '0') {
                    $op = 'in';
                    $val = implode(',',$filter);
                }
            } else {
                $op='='; $val = $filter;
            }
        ?>
        <tr>
            <td>
                <select name="<:classname:>[filter][fields][]">
<:option_fields:>
                </select>
            </td>
            <td>
                <select name="Posts[filter][op][]">
                    <option value="=" <?=($op=='=' ? 'selected="selected"' : '')?> >=</option>
                    <option value="<" <?=($op=='<' ? 'selected="selected"' : '')?>>&lt;</option>
                    <option value=">" <?=($op=='>' ? 'selected="selected"' : '')?>>&gt;</option>
                    <option value="<=" <?=($op=='<=' ? 'selected="selected"' : '')?>>&lt;=</option>
                    <option value=">=" <?=($op=='>=' ? 'selected="selected"' : '')?>>&gt;=</option>
                    <option value="like" <?=($op=='like' ? 'selected="selected"' : '')?>>LIKE</option>
                    <option value="in" more="enum" <?=($op=='in' ? 'selected="selected"' : '')?>>IN</option>
                    <option value="!in" more="enum" <?=($op=='!in' ? 'selected="selected"' : '')?>>NOT IN</option>
                    <option value="between" more="2fields" <?=($op=='between' ? 'selected="selected"' : '')?>>BETWEEN</option>
                </select>
            </td>
            <td>
                <input type="text" name="<:classname:>[filter][value1][]" value="<?=$val?>" />
                <input type="text" name="<:classname:>[filter][value2][]" value="" class="hidden" />
            </td>
            <td>
                <a href="#" class="dotted addcondition">Еще</a>
                <select name="Posts[filter][link][]" class="hidden">
                    <option value="and">И</option>
                    <option value="or">ИЛИ</option>
                    <option value="and not">И НЕ</option>
                </select>
            </td>
        </tr>
        <? endif; ?>
    <? endforeach; ?>
    <? $key = ''; ?>
    <tr>
        <td>
            <select name="<:classname:>[filter][fields][]">
<:option_fields:>
            </select>
        </td>
        <td>
            <select name="<:classname:>[filter][op][]">
                <option value="=">=</option>
                <option value="<">&lt;</option>
                <option value=">">&gt;</option>
                <option value="<=">&lt;=</option>
                <option value=">=">&gt;=</option>
                <option value="like">LIKE</option>
                <option value="in" more="enum">IN</option>
                <option value="!in" more="enum">NOT IN</option>
                <option value="between" more="2fields">BETWEEN</option>
            </select>
        </td>
        <td>
            <input type="text" name="<:classname:>[filter][value1][]" value="" />
            <input type="text" name="<:classname:>[filter][value2][]" value="" class="hidden" />
        </td>
        <td>
            <a href="#" class="dotted addcondition">Еще</a>
            <select name="Posts[filter][link][]" class="hidden">
                <option value="and">И</option>
                <option value="or">ИЛИ</option>
                <option value="and not">И НЕ</option>
            </select>
        </td>
    </tr>
    </table>
    <input type="submit" value="Искать" />
    <a href="<:websubdir:>?<:classname:>[clear]=1">Сбросить фильтр</a></li>
    </form>
    </div>    
</div>
<script type="text/javascript">
$(document).ready(function(){
    $('.addcondition').click(function(){
        $('#filterheadercont').after( $(this).parent().parent().clone() );
        return false;
    });
});
</script>


<div class="panel panel-primary">
    <div class="panel-heading"><:classname:></div>
    <div class="panel-body">
        <p>
            Найдено: <?php echo $this->iPageSize < $this->iCntModels ? $this->iPageSize : $this->iCntModels; ?> из <?php echo $this->iCntModels; ?>
        &rarr; <a href="<:websubdir:>create/">Создать</a>
        </p>
        <br />
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Действия</th>
<:sort_fields:>
                </tr>
            </thead>
            <tbody>
                <?php foreach($this->arItems as $iPos => $oModel): ?>
                <tr valign="top">
                    <td>
                        <a href="<:websubdir:>edit/?<:id_name:>=<=$oModel-><:id_name:>?>" button="role" class="btn btn-default btn-xs mb5">Изменить</a>
                        <br />
                        <a href="<:websubdir:>del/?<:id_name:>=<?=$oModel-><:id_name:>?>" onclick="confirm('Вы действительно хотите удалить?');"  role="button" class="btn btn-xs btn-danger">Удалить</a>
                    </td>
<:value_fields:>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="sfw_paging">
        <?php
        if( $this->paging )
        {
            echo \IslandFuture\Sfw\Application::one()->block('paging',
                array(
                    'size'=>$this->iPageSize,
                    'current'=>$this->iPage,
                    'total_rec'=> $this->iCntModels
                )
            );
        }
        ?>
        </div>

    </div>

</div>


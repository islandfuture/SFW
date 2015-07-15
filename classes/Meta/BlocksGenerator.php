<?php
namespace IslandFuture\Sfw\Meta;

/**
 * Класс предназначен для генерации различного рода блоков по мета-модели
 *
 * @author  Michael Akimov <michael@island-future.ru>
 * @version GIT: $Id$
 */
class BlocksGenerator extends Generator
{
    // declared in parents: protected $arParams = array();

    public function generate()
    {
        
        if (! file_exists($this->sPathMetaGen.$this->sClassname)) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname."\n";
            mkdir($this->sPathMetaGen.$this->sClassname);
        }

        if (! file_exists($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'blocks')) {
            echo "Creade dir: ".$this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR."blocks\n";
            mkdir($this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'blocks');
        }
        
        if (! $this->arFields || sizeof($this->arFields) == 0) {
            echo "Not found field for model [".$this->sClassname."]\n";
            return false;
        }

        if ($this->sWebSubDir == '') {
            $this->sWebSubDir = '/admin/'.strtolower($this->sClassname).'/';
        }
        
        $this->saveBlockInfo();
        $this->saveBlockDel();
        $this->saveBlockList();
    }
    
    public function saveBlockList()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['classlower'] = strtolower($this->sClassname);
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        $arVars['websubdir'] = $this->sWebSubDir;
        
        $arVars['_class_'] = strtolower($this->sClassname);

        $arVars['value_fields'] = '';
        $arVars['sort_fields'] = '';
        $arVars['option_fields'] = '';
        $arVars['defaults'] = '';
        $arVars['relations_fields'] = '';

        $arVars['primary_type'] = '(int)';
        
        $arFieldRelations = array();
        /* перебираем все отношения и связываем их с полями */
        foreach ($this->arRelations as $sRelation => $arRelation) {
            $sKey = $arRelation[1];
            if (isset($this->arFields[$sKey])) {
                $arFieldRelations[$sKey] = $arRelation;
                $arFieldRelations[$sKey]['sRelName'] = $sRelation;
            }
        }
        
        
        foreach ($this->arFields as $sField => $arField) {
            $sTitle = (isset($arField['sTitle']) ? $arField['sTitle'] : $sField);
            $arVars['option_fields'] .= "                ".'<option value="'.$sField.'" <?=($key==\''.$sField.'\' ? \'selected="selected"\' : \'\')?>>'.$sTitle.'</option>'."\n";
            
            if (isset($arField['sPrimary']) && 'yes'==$arField['sPrimary']) {
                $arVars['id_name'] = $sField;
                if ('char' == $arField['sType'] || 'varchar' == $arField['sType']) {
                    $arVars['primary_type'] = '(string)';
                }
            }
            
            if (($arField['sType']!='varchar' && $arField['sType']!='text') || ($arField['sType']=='varchar' && $arField['iLength'] < 257)) {

                $arVars['sort_fields'] .= '                    <th><a href="?sort='.$sField.'&dir=<?=isset($this->sort[\''.$sField.'\']) && $this->sort[\''.$sField.'\']==\'asc\' ? \'desc\' : \'asc\' ?>">'.$sTitle.'</a></th>'."\n";
                $arVars['value_fields'] .= '                    <td><?=$oModel->'.$sField.'; ?></td>'."\n";
            }
        }
        
        $arFiles = $this->getListFilesEx($this->sPathMetaTemplates.'blocks'.DIRECTORY_SEPARATOR.'_class_.list'.DIRECTORY_SEPARATOR, 0, $this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.$arVars['classlower'].'.list'.DIRECTORY_SEPARATOR, $arVars);

        foreach($arFiles as $sParse => $sFile) {
            \IslandFuture\Sfw\Template::one()->parse($sParse, $arVars);
            echo 'Save file: '.$sFile."\n";
            \IslandFuture\Sfw\Template::one()->saveTo($sFile);
        }
    }
    
    public function saveBlockDel()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['classlower'] = strtolower($this->sClassname);
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        $arVars['websubdir'] = $this->sWebSubDir;

        $arVars['_class_'] = strtolower($this->sClassname);

        $arVars['clear_fields'] = '';
        $arVars['defaults'] = '';
        $arVars['relations_fields'] = '';

        $arVars['primary_type'] = '(int)';
        foreach ($this->arFields as $sField => $arField) {
            
            if (isset($arField['sPrimary']) && 'yes'==$arField['sPrimary']) {
                $arVars['id_name'] = $sField;
                if ('char' == $arField['sType'] || 'varchar' == $arField['sType']) {
                    $arVars['primary_type'] = '(string)';
                }
            }
        }

        $arFiles = $this->getListFilesEx($this->sPathMetaTemplates.'blocks'.DIRECTORY_SEPARATOR.'_class_.del'.DIRECTORY_SEPARATOR, 0, $this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.$arVars['classlower'].'.del'.DIRECTORY_SEPARATOR, $arVars);

        foreach($arFiles as $sParse => $sFile) {
            \IslandFuture\Sfw\Template::one()->parse($sParse, $arVars);
            echo 'Save file: '.$sFile."\n";
            \IslandFuture\Sfw\Template::one()->saveTo($sFile);
        }
    }

    public function saveBlockInfo()
    {
        $arVars = array();
        $arVars['classname'] = $this->sClassname;
        $arVars['classlower'] = strtolower($this->sClassname);
        $arVars['tablename'] = $this->sTablename;
        $arVars['database'] = $this->sDatabase;
        $arVars['titlename'] = $this->sTitlename;

        $arVars['websubdir'] = $this->sWebSubDir;

        $arVars['_class_'] = strtolower($this->sClassname);

        $arVars['clear_fields'] = '';
        $arVars['defaults'] = '';
        $arVars['relations_fields'] = '';

        $arVars['primary_type'] = '(int)';
        
        $arFieldRelations = array();
        /* перебираем все отношения и связываем их с полями */
        foreach ($this->arRelations as $sRelation => $arRelation) {
            $sKey = $arRelation[1];
            if (isset($this->arFields[$sKey])) {
                $arFieldRelations[$sKey] = $arRelation;
                $arFieldRelations[$sKey]['sRelName'] = $sRelation;
            }
        }
        
        /* перебирем все поля */
        foreach ($this->arFields as $sField => $arField) {
            if (empty($arField['sTitle'])) {
                $arField['sTitle'] = $sField;
            }

            if (isset($arField['sPrimary']) && 'yes'==$arField['sPrimary']) {
                $arVars['id_name'] = $sField;
                if ('char' == $arField['sType'] || 'varchar' == $arField['sType']) {
                    $arVars['primary_type'] = '(string)';
                }
            } else {

                if ((isset($arField['iLength']) && $arField['iLength'] > 256) || ('text' == $arField['sType'])) {
                    $iRows = ('text' == $arField['sType'] ? 7 : floor($arField['iLength']/128));
                    $arVars['clear_fields'] .= <<<EOT

    <div class="form-group">
        <label class="col-sm-2 control-label" for="{$arVars['classname']}[{$sField}]">{$arField['sTitle']}</label>
        <div class="col-sm-7">
            <textarea name="{$arVars['classname']}[{$sField}]" class="form-control" rows="{$iRows}"><?=\$this->oModel->{$sField}?></textarea>
        </div>
    </div>
            
EOT;
                } elseif (isset($arFieldRelations[$sField]) && 'ONE' == $arFieldRelations[$sField][0]) {
                    $sRelation = $arFieldRelations[$sField]['sRelName'];
    
                    $arVars['clear_fields'] .= <<<EOT

    <div class="form-group">
        <label class="col-sm-2 control-label" for="{$arVars['classname']}[{$sField}]">{$arField['sTitle']}</label>
        <div class="col-sm-7">
            <input  class="form-control" type="text" name="{$arVars['classname']}[{$sField}]" value="<?=\$this->oModel->{$sField}?>" aria-describedby="help{$arVars['classname']}[{$sField}]" />
            <?php if(\$this->oModel->{$sRelation}()): ?>
                <span id="help{$arVars['classname']}[{$sField}]" class="help-block"><?=\$this->oModel->{$sRelation}()->getName()?></span>
            <?php endif; ?>
        </div>
    </div>

EOT;
                } elseif (isset($arFieldRelations[$sField]) && 'VIRTUAL' == $arFieldRelations[$sField][0]) {
                    $sRelation = $arFieldRelations[$sField]['sRelName'];
    
                    $arVars['clear_fields'] .= <<<EOT

    <div class="form-group">
        <label class="col-sm-2 control-label" for="{$arVars['classname']}[{$sField}]">{$arField['sTitle']}</label>
        <div class="col-sm-7">
            <select class="form-control" name="{$arVars['classname']}[{$sField}]">
                <?=\$this->oModel->getOptionsList('{$sRelation}', \$this->oModel->{$sField})?>
            </select>
        </div>
    </div>

EOT;
                } else {
                    $arVars['clear_fields'] .= <<<EOT

    <div class="form-group">
        <label class="col-sm-2 control-label" for="{$arVars['classname']}[{$sField}]">{$arField['sTitle']}</label>
        <div class="col-sm-7">
            <input  class="form-control" type="text" name="{$arVars['classname']}[{$sField}]" value="<?=\$this->oModel->{$sField}?>" />
        </div>
    </div>
            
EOT;
                }
            }
        }
        /* end foreach */
        
        $arFiles = $this->getListFilesEx($this->sPathMetaTemplates.'blocks'.DIRECTORY_SEPARATOR.'_class_.info'.DIRECTORY_SEPARATOR, 0, $this->sPathMetaGen.$this->sClassname.DIRECTORY_SEPARATOR.'blocks'.DIRECTORY_SEPARATOR.$arVars['classlower'].'.info'.DIRECTORY_SEPARATOR, $arVars);

        foreach($arFiles as $sParse => $sFile) {
            \IslandFuture\Sfw\Template::one()->parse($sParse, $arVars);
            echo 'Save file: '.$sFile."\n";
            \IslandFuture\Sfw\Template::one()->saveTo($sFile);
        }
    }
    
    /**
     * @return Array
     */
    public function getListFilesEx($sPath, $iDepth, $sNewPath, $arVars=array())
    {
        return \IslandFuture\Sfw\Tools::getListFilesEx($sPath, $iDepth, $sNewPath, $arVars);
    }
}

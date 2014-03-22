<tr id="<?php echo $this->_row->id;?>" class="<?php echo $this->_row->class;?>">
	<?php foreach ($this->headings as $heading => $label) {
		$style = empty($this->cellClass[$heading]['style']) ? '' : 'style="'.$this->cellClass[$heading]['style'].'"';?>
		<td class="<?php echo $this->cellClass[$heading]['class']?>" <?php echo $style?>>

		<?php if ($heading == 'po_spotlight___image') {
			if ($this->_row->data->$heading == '') {
				echo FabrikHelperHTML::image('image-grey.png', 'list', $this->tmpl, 'no image');
			} else {
				$rawheading = $heading."_raw";
				$imgData = json_decode($this->_row->data->$rawheading);

				$this->xmlDoc = JFactory::getXML('Simple');
				$ok =	$this->xmlDoc->loadString('<xml>'.$this->_row->data->$heading.'</xml>');
				if ($ok) {
					$img = $this->xmlDoc->document->getElementByPath('/a/img');
					$title = htmlentities($img->toString(), ENT_QUOTES);
				} else {
					$title = 'not found';
				}
				$file = $imgData[0]->file;
				//title="'.basename($file).'"

				echo '<a class="fabrikTip" opts="{position:\'left\'}" title="'.$title.'" rel="lightbox[]" href="'.COM_FABRIK_LIVESITE.$file.'">';
				//class="fabrikLightBoxImage"
				echo FabrikHelperHTML::image('image.png', 'list', $this->tmpl, 'image');
				echo '</a>';
			}
	}else {
		echo @$this->_row->data->$heading;
	}?>
		</td>
	<?php }?>
</tr>
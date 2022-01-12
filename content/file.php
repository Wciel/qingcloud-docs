<?php

class File extends SplFileInfo{

	protected $path = '';
	protected $pFile = null;

	public function __construct($path)
	{
		$this->path = $path;	
		parent::__construct($path);
	}

	public function getPFile()
	{
		$this->pFile =  new self($this->getParentDirectory());
		return $this->pFile;
		
	}

	// 获取同级文件
	public function  getSiblingsFiles()
	{
		return $this->getPFile()->getFiles();
	}

	// 获取同级目录
	public function getSiblingsDirectory()
	{
		return $this->getPFile()->getDirectories();
	}

	public function getParentDirectory()
	{
		return  '..'.DS.$this->path;
		$pathinfoArray =  explode(DS,$this->path);
		array_pop($pathinfoArray);
		$parentPath = implode(DS,$pathinfoArray);
		return $parentPath;
	}

	public function getWeight()
	{
		if($this->isDir()){
			$weight = $this->getDirectoryWeight();
		}else{
			$weight = $this->getFileWeight($this->path);
		}
		return $weight;
	}

	public function getTitle()
	{
		return $this->getFileTitle($this->path);
	}

	/*
	 *  获取是不是草稿
	 * TODO => 如果是上级文件为草稿， 子文件也不应该显示
	 */
	public function checkIsDraft()
	{
		$content = file_get_contents($this->path);
		preg_match('/draft: (\S+)/',$content,$dmatch);

		return (!empty($dmatch) && $dmatch[1] == 'true') ? true : false;
	}

	protected function getDirectoryWeight()
	{
		$files = $this->getFiles();
		foreach($files as $f){
			$fileObj =  new SplFileInfo($f);
			$fileName = $fileObj->getFileName();
			if($fileName==='_index.md'){
				$obj = new self($fileObj->getRealPath());
				return $obj->getWeight();
			}
		}
		return 0;
	}

	public function getFileTitle($filepath)
	{
		$content = file_get_contents($this->path);
		preg_match('/title: "(\S+)"/',$content,$tmatch);

		if(empty($tmatch[1])){
			// $this->error("文件%s:获取title错误",[$this->path]);
			return  0 ;
		}
		return $tmatch[1];
	}

	public function getFileWeight($filepath)
	{
		if (!is_file($filepath)) return 0;
		$content = file_get_contents($filepath);
		preg_match('/weight: (\d+)/',$content,$wmatch);
		if(empty($wmatch[1])){
			// $this->error("文件%s:获取weight错误",[$filepath]);
			return  0 ;
		}
		return $wmatch[1];
	}

	// alias 当前目录下的所有目录
	public function getChildDirectory()
	{
		return $this->getDirectories();
	}
	// alias 当前目录下的所有文件
	public function getChildFiles()
	{
		return $this->getFiles();
	}
	
	public function isHasChildDirectory(){
		if($this->isDir()){
			$childDirs = $this->getChildDirectory();
			return !empty($childDirs);
		}
		return false;
	}

	public function getFiles()
	{
		return  $this->getFilesOrDirectories(true);
	}

	public function getDirectories()
	{
		return $this->getFilesOrDirectories(false);
	}

	private function getFilesOrDirectories($isFile=true)
	{
		$dir = $this->path;
		$returnDirs =$files = [];
		$rfp = opendir($dir);

		if (!readdir($rfp)) {
			var_dump($this->path);
			die();
		}
		while(($file=readdir($rfp))!==false){
			if($file==='.'||$file==='..') continue;
			$path =  $dir.DS.$file;
			if(is_dir($path)){
				$returnDirs[]= $path;
			}else{
				if((new self($path))->getExtension()==='md')
					$files [] = $path;
			}
		}
		return  $isFile?$files:$returnDirs;
	}

	// 获取文件夹中里面最小weight的
	public function dirSortInfo($sort = '')
	{
		$childrens = $this->getChildDirectory();

		$ret = [];

		foreach ($childrens as $k) {

			if ($k == './pdf') continue;
			$ret[] = [
				'weight'	=>	$this->getFileWeight($k. '/_index.md'),
				'path'		=>	$k
			];
		}

		if ($sort == 'asc') {
			$ret = $this->arraySort($ret, 'weight', SORT_ASC);
		}

		return $ret;

	}

	// 排序文件夹
	public function fileSortInfo($files)
	{
		$ret = [];

		foreach ($files as $file) {
			if (substr(strrchr($file, '.'), 1)  != 'md') continue;

			$weight =  (substr($file, strpos($file, '_index.md')) === '_index.md') ? 0 : $this->getFileWeight($file);
			$ret[] = [
				'weight'	=>	$weight,
				'path'		=>	$file
			];
		}

		$ret = $this->arraySort($ret, 'weight', SORT_ASC);

		return $ret;

	}


	/**
	 * 二维数组根据某个字段排序
	 * @param array $array 要排序的数组
	 * @param string $keys   要排序的键字段
	 * @param string $sort  排序类型  SORT_ASC     SORT_DESC 
	 * @return array 排序后的数组
	 */
	function arraySort($array, $keys, $sort = SORT_DESC) {
		$keysValue = [];
		foreach ($array as $k => $v) {
			$keysValue[$k] = $v[$keys];
		}
		array_multisort($keysValue, $sort, $array);
		return $array;
	}

}


?>

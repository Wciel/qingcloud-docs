<?php
/**
* Class md
* @author batcom
*/
define('DS',DIRECTORY_SEPARATOR);
include 'file.php';
class md
{
	protected $resultFileHandle = null;
	protected $fileNum=0;
	protected $parentDir='.';
	protected $directoryNum=0;

	protected $files = [];

	protected $content = '';

	public function run($path='.')
	{
		if(is_file('result.md')) unlink('result.md');

		$this->resultFileHandle = fopen('result.txt','ab+');
		$this->listDirectory($path);
		$this->streamWrite($this->files);
		fclose($this->resultFileHandle);
		rename('result.txt','result.md');
	}

	/**
	 * 排序文件, 根据 weight 值得出排序过后的文件
	 *
	 * @param $dir
	 */
	public function listDirectory($dir)
	{
		$file =  new File($dir);

		$sort_info = $file->dirSortInfo('asc');

		foreach ($sort_info as $info) {

			$fileObj = new File($info['path']);

			$files = ($fileObj)->getFiles();

			if (!empty($files)) {
				$sort_files = $fileObj->fileSortInfo($files);

				foreach ($sort_files as $v) {
					array_push($this->files, $v['path']);
				}
			}

			if (is_dir($info['path'])) {
				$this->listDirectory($info['path']);
			}
		}

	}

	/**
	 *
	 * 写入文件
	 *
	 * @param $files
	 */
	public function streamWrite($files)
	{
		foreach($files as $k=>$file){

			if (!$this->checkNeedWriteFile($file)) continue;

			$fp = $this->adjustFileContent($file);

			stream_copy_to_stream($fp,$this->resultFileHandle);
			fclose($fp);
		}

	}

	/**
	 * 过滤一些没有必要写的文件
	 *
	 * @param $path
	 * @return bool
	 */
	public function checkNeedWriteFile($path)
	{

		$fileObj = new File($path);

		if ($path == './features.md'
		|| $fileObj->checkIsDraft() === true
		|| $fileObj->getTitle() === '产品动态'
		|| $fileObj->getTitle() === '动态与公告'
		) {
			return false;
		}

		return true;
	}

	/**
	 *
	 * 调整文件 => 1. 过滤掉文件中的  头部介绍
	 *
	 * @param $path
	 * @return bool|resource
	 */
	public function adjustFileContent($path)
	{
		$fp = fopen('php://memory', 'r+');

		if (substr($path, strpos($path, '_index.md')) === '_index.md') {

			$count =  substr_count($path, '/');

			// 其他的需要找到对应的标题
			$title = (new File($path))->getTitle();

			$content = '';

			if ($count > 2 && $title) {
				$content = PHP_EOL . str_repeat('#', $count - 1) . ' ' . $title . PHP_EOL;
			} else if ($title) {
				$content = PHP_EOL . '# '. $title . PHP_EOL;
			}

		} else {
			
			$content = file_get_contents($path);
			// 处理头部 --- --- 内容
			$content = $this->dealWithHeadContent($content);
			// 处理图片展示
			$content = $this->dealWithContentPic($content, $path);
		}

		fputs($fp, $content);
		rewind($fp);

		return $fp;
	}

	/**
     *
     * 工具函数  =>  查找字符串中第几次出现字符的位置
     *
     *
     * @param $str
     * @param $find
     * @param $n
     * @return bool|int
     */
    private function str_n_pos($str, $find, $n)
    {
        $pos_val=0;
        for ($i=1;$i<=$n;$i++){

            $pos = strpos($str,$find);

            $str = substr($str,$pos+1);

            $pos_val=$pos+$pos_val+1;

        }
        return $pos_val-1;
    }

	// 去除头部 展示

	/**
	 * @param $content
	 * @return mixed|string|string[]|null
	 */
	public function dealWithHeadContent($content)
	{

		// 去掉头部  --- --- 中间的部分
		$content = substr_replace($content,
			'',
			$this->str_n_pos($content, '---', 1),
			$this->str_n_pos($content, '---', 2) + 3
		);

		$content = preg_replace_callback('~#(#| ).*~', function ($matches) {
			$origin_str = $matches[0];
			$count = substr_count($origin_str, '#');
			return str_replace(str_repeat('#', $count), str_repeat('#', $count + 1), $origin_str);
		}, $content);

		return $content;
	}

	/**
     *
     * 处理图片
     *
     * @param $content
     * @param $path
     * @return mixed
     */
    private function dealWithContentPic($content, $path)
    {
        $preg = '~([/|.|\w|\s|\-|\+])*\.(?:jpg|png|gif)~';
        preg_match_all($preg, $content, $matches);

        if (!empty($matches[0])) {
            foreach($matches[0] as $url) {
                if (strpos($url, '../') === 0) {
                    $replace_path = substr($path,
                        strpos($path, './') + 1,
                        $this->str_n_pos(
                            $path,
                            '/',
                            count(explode('/', $path)) - substr_count($url, '../')
                        ) - strpos($path, './'));

                    $origin_str = str_repeat('../', substr_count($url, '../'));

                    $content = str_replace($origin_str, $replace_path, $content);

                }
            }
        }

        return $content;
    }

	
}

$md =  new Md();
$md->run();

?>

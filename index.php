<?php
	
	function clean_local_directory() {
		exec("rm -rf /home/mercury/watermark/uploads/*");
		exec("rm -rf /home/mercury/watermark/watermarked.zip");
	}

	function watermark($directory_src) {

		$parent_dir="/home/mercury/watermark/";
		$watermark_src=$parent_dir."watermark.png";
		$watermark_tmp_src=$parent_dir."watermark_tmp.png";

		$Directory = new RecursiveDirectoryIterator($directory_src.'/');
		$Iterator = new RecursiveIteratorIterator($Directory);

		$directory_name=substr(strrchr(rtrim($directory_src,"/"),"/"), 1);

		$Regex = new RegexIterator($Iterator, '/^.+\.jpg$/i', RecursiveRegexIterator::GET_MATCH);

		foreach($Regex as $images) {
			foreach($images as $image_src) {
				$image = new Imagick($image_src);
				$d = $image->getImageGeometry();

				exec("convert ".$watermark_src." -resize ".$d['width']."x".$d['height']." watermark_tmp.png");
				exec("composite -gravity center ".$watermark_tmp_src." '".$image_src."' '".$image_src."'");
			}
		}

		exec("cd ".$directory_src."/.. ;zip -r ".$parent_dir."watermarked.zip"." ".$directory_name.";");
	}

	class FileUploader{
		public function __construct($uploads,$uploadDir='/home/mercury/watermark/uploads/'){
			foreach($uploads as $current)
			{
				$this->uploadFile=$uploadDir.$current->name;
				if($this->upload($current,$this->uploadFile)){
#					echo "Successfully uploaded ".$current->name."n";
				}
			}
		}
		
		public function upload($current,$uploadFile){
			if(move_uploaded_file($current->tmp_name,$uploadFile)){
				return true;
			}
		}
	}



	function UpFilesTOObj($fileArr){
		foreach($fileArr['name'] as $keyee => $info)
		{
			$uploads[$keyee]->name=$fileArr['name'][$keyee];
			$uploads[$keyee]->type=$fileArr['type'][$keyee];
			$uploads[$keyee]->tmp_name=$fileArr['tmp_name'][$keyee];
			$uploads[$keyee]->error=$fileArr['error'][$keyee];
		}
		return $uploads;
	}


	function get_file_extension($file_name)
	{
	  return substr(strrchr($file_name,'.'),1);
	}


	if($_SERVER['REQUEST_METHOD']=="POST") {

		if(isset($_FILES['file_input'])) {

			clean_local_directory();			
			$uploads = UpFilesTOObj($_FILES['file_input']);
			
			$fileUploader=new FileUploader($uploads);
			exec("rm -rf /home/mercury/watermark/uploads/.*");
			watermark("/home/mercury/watermark/uploads/");
			$success=TRUE;
		}else {
			$success=FALSE;
		}
	}else {
		$success=FALSE;
	}
?>
<html>
	<head>
		<title>Watermark Equiphunt Logo</title>
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script   src="https://code.jquery.com/jquery-3.1.0.slim.min.js"   integrity="sha256-cRpWjoSOw5KcyIOaZNo4i6fZ9tKPhYYb6i5T9RSVJG8="   crossorigin="anonymous"></script>
	</head>
	<body>
		<script type="text/javascript">
			function download_file() {
				window.open("/watermarked.zip");
			}
		</script>
		<center>
		<div class="container">
			<div id="top" style="margin-top: 50px;">
				<img src="https://www.equiphunt.com/ops/Resources/images/logo.png"/>
				<h1> Add watermark to photos in bulk </h1>
			</div>
			<div id="middle">
				<h4 class="text-danger" style="margin-left: 50%;">*Only supports jpg files</h4>
				<form name="watermark" method="POST" action="index.php" enctype="multipart/form-data">
					<fieldset>
						<div class="form-group">
							Step 1: Select your Folder: 
							<label class="btn btn-primary btn-file glyphicon glyphicon-folder-open">
								Choose Folder<input style="display:none;" type="file" name="file_input[]" id="file_input" multiple webkitdirectory="">
							</label>
						</div>
						<div class="form-group">
							 Step 2: Press upload button:
							<label>
								<input class="btn btn-warning glyphicon glyphicon-upload" type="submit" value="Upload"/>
							</label>
						</div>
					</fieldset>
				</form>
				<?php 
					if($success==TRUE) {
				?>
					 
					<label>
						<div class="form-group">
							<span>Congrats your files are ready</span>
						</div>
						<button class="btn btn-success glyphicon glyphicon-download" onclick="download_file();"> Download your files </button>
					</label>
				<?php
					}
				?>
			</div><br>
			<div id="bottom">
				 Copyright &#169; Sociam Equipment Solutions Pvt. Ltd.
			</div>
		</div>
		</center>
	</body>
</html>
<?php

namespace Config\MediaBundle\Entity;

use Config\MediaBundle\Lib\FileManager;
use Config\MediaBundle\Model\File;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Bridge\Doctrine\Validator\Constraints as DoctrineAssert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty as VirtualProperty;
/**
 * Class Media
 * @package Config\Media\Entity
 * @ORM\HasLifecycleCallbacks
 * @ORM\Table(name="media")
 * @DoctrineAssert\UniqueEntity(fields="name", message="This name already exists")
 * @ORM\Entity(repositoryClass="Config\MediaBundle\Entity\Repository\MediaRepository")
 * @Gedmo\TranslationEntity(class="Config\MediaBundle\Entity\Translations\MediaTranslations")
 */
class Media extends File  implements  Translatable
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     * @ORM\Column(type="string", unique=true, length=255, nullable=false)
     */
    protected $name;

    /**
     * @var string $realName
     * @ORM\Column(type="string", length=255, nullable=false)
     */
    protected $realName;

    /**
     * @var string $title
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string $description
     * @Gedmo\Translatable
     * @ORM\Column(type="text", nullable=true)
     */
    protected $description;

    /**
     * @var string $caption
     * @Gedmo\Translatable
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $caption;

    /**
     * @var string $alt
     * @ORM\Column(type="string", length=200, nullable=true)
     */
    protected $alt;

    /**
     * @var integer $width
     * @ORM\Column(type="integer",length=11, nullable=true)
     */
    protected $width;

    /**
     * @var integer $height
     * @ORM\Column(type="integer",length=11, nullable=true)
     */
    protected $height;

    /**
     * @var string $size
     * @ORM\Column(type="string",length=50, nullable=true)
     */
    protected $size;

    /**
     * @var string $type
     * @ORM\Column(type="string", length=180, nullable=true)
     */
    protected $type;

    /**
     * @var string $icon
     * @ORM\Column(type="string",length=100, nullable=true)
     */
    protected $icon = '';

    /**
     * @var integer $position
     * @ORM\Column(type="integer",length=11, nullable=true)
     */
    protected $position = 0;
    
    /**
     * @Groups({"filter"})
     * @var string $path
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @var string $context
     * @ORM\Column(type="string", length=155, nullable=true)
     */
    protected $context;

    /**
     * @var $gallery
     * @ORM\ManyToMany(targetEntity="Gallery", mappedBy="media",  cascade={"persist"})
     */
    protected $gallery;

    protected $cropSize;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\OneToMany(targetEntity="Config\MediaBundle\Entity\Translations\MediaTranslations", mappedBy="object", cascade={"persist", "remove"})
     */
    protected $translations;

    /**
     * @Gedmo\Locale
     * Used locale to override Translation listener`s locale
     * this is not a mapped field of entity metadata, just a simple property
     */
    private $locale;
    
    public function __toString()
    {
        return ($this->name) ? $this->name : '';
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
        return __DIR__.'/../../../../web/'.FileManager::getUploadDir();
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getAbsoluteCropPath($size,$name)
    {
        return null === $this->path
            ? null
            : '/'.  $this->getUploadRootDir().'/'.$size.'_'.$name;
    }

    public function getAbsoluteCropWebPath($size,$name)
    {
        return null === $this->path
            ? null
            : '/'.  FileManager::getUploadDir().'/'.$size.'_'.$name;
    }


    public function getWebPath()
    {
        return null === $this->path
            ? null
            : '/'.FileManager::getUploadDir().'/'.$this->path;
    }
    
    public function isMedia(){
        return true;
    }

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     * @throws \Exception
     */
    public function setFile( $file = null)
    {
        if($file->getError()){
            throw new \Exception('Something went wrong!');
        }
        $this->file = $file;
        if (isset($this->path)) {
            $this->temp = $this->path;
            $this->path = null;
        } else {
            $this->path = 'initial';
        }
    }

    protected function setParameters()
    {
        $path = $this->file;
        $file_extension = $this->getFile()->guessExtension();
        if($file_extension == NULL){
            $extension =  explode('.',$this->getFile()->getClientOriginalName());
            $file_extension = end($extension);
        }
        $extensions = FileManager::getExtensions();
        $icons = $this->getFileIcons();
        list($width,$height) = getimagesize($path);
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mtype = finfo_file($finfo, $path);
        
        $this->setType($mtype);
        $this->setWidth($width);
        $this->setHeight($height);

        if(filesize($path) > 1024 * 1024){
            $this->setSize(round(filesize($path) / pow(10,6),2)." mb");
        }
        else{
            $this->setSize(round(filesize($path) / pow(10,3),2)." kb");
        }
        $file_type = explode('/',$mtype);
       
        $this->setIcon($icons[$file_extension]);

    }

    public function getContexts()
    {
        return FileManager::getCropImageSettings();
    }

    protected function checkCropSize($contexts)
    {
        if(array_key_exists ($this->context, $contexts)){
            $this->cropSize = $contexts[$this->context];
        }
        else{
            $this->cropSize = null;
        }
    }


    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {

        if (null !== $this->getFile()) {
            $this->setParameters();
            // do whatever you want to generate a unique name
            $filename = sha1(uniqid(mt_rand(), true));
            $this->path = $filename.'.'.$this->getFile()->guessExtension();
            $originalName = substr ($this->file->getClientOriginalName(), 0, strrpos($this->file->getClientOriginalName(), '.'));
            $this->setRealName($originalName);
            //$em = $this->container->get('doctrine')->getManager();
            $now =  new \DateTime();
            $date = $now->format('dmYHi').mt_rand(100, 9999);
            $this->setName($originalName.$date.'.'.$this->getFile()->guessExtension());
        }
    }

    /**
     * @ORM\PostPersist()
     * @ORM\PostUpdate()
     */
    public function upload()
    {

        if (null === $this->getFile()) {
            return;
        }
        $this->checkCropSize($this->getContexts());
        $size = $this->cropSize['formats'];
        $size['admin_thumb'] = array('width'=> 150,'quality'=>70);
        $newFile = $this->getName();
        $this->getFile()->move($this->getUploadRootDir(), $this->path);
        $path = $this->getUploadRootDir().'/'.$this->path;

        foreach($size as $key=>$value){
            $newPath = $this->getUploadRootDir().'/'.$key.'_'.$newFile;
                $this->imageResize(
                $path,
                $newPath,
                $value['width'],
                null,
                $value['quality']
            );
        }

        if (isset($this->temp)) {
            // delete the old image
            unlink($this->getUploadRootDir().'/'.$this->temp);
            // clear the temp image path
            $this->temp = null;
        }
        $this->file = null;
    }


    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        $this->checkCropSize($this->getContexts());
        $size = $this->cropSize['formats'];
        $type = array_keys($size);
        array_push($type,'admin_thumb');
        
        $file = $this->getAbsolutePath();

        if(file_exists($file)){
            $name = $this->name;
            foreach($type as $value){
                if(file_exists($this->getAbsoluteCropPath($value,$name))){
                    unlink($this->getAbsoluteCropPath($value,$name));
                }
            }
        }

        if ($file) {
            unlink($file);
        }
    }


    function imageResize($path,$newPath,$width,$height,$quality)
    {
        if($path){
            ini_set("gd.jpeg_ignore_warning", 1);

            list($w_i, $h_i, $type) = getimagesize($path);
            if (!$w_i || !$h_i) {
                return;
            }
            if($w_i <  $width ){
                return;
            }
            $types = array('','gif','jpeg','png');

            $ext = $types[$type];
            if ($ext) {
                $func = 'imagecreatefrom'.$ext;
                $img = $func($path);
            } else {
                return;
            }
            

            if($height == null && $width == null){

                $img_o = imagecreatetruecolor($w_i, $h_i);
                imagealphablending($img_o, false);
                imagesavealpha($img_o, true);
                $transparent = imagecolorallocatealpha($img_o, 255, 255, 255, 127);
                imagefilledrectangle($img_o, 0, 0, $width, $height, $transparent);
                imagecopyresampled($img_o, $img, 0, 0, 0, 0, $w_i, $h_i, $w_i, $h_i);
            }
            else{
                if (!$height){
                    $height = $width/($w_i/$h_i);
                }
                if (!$width) {
                    $width = $height/($h_i/$w_i);
                }
                $img_o = imagecreatetruecolor($width, $height);

                imagealphablending($img_o, false);
                imagesavealpha($img_o, true);
                $transparent = imagecolorallocatealpha($img_o, 255, 255, 255, 127);
                imagefilledrectangle($img_o, 0, 0, $width, $height, $transparent);

                imagecopyresampled($img_o, $img, 0, 0, 0, 0, $width, $height, $w_i, $h_i);
            }

            if ($type == 2) {
                return imagejpeg($img_o,$newPath,$quality);
            }
            else {
                $func = 'image'.$ext;
                return $func($img_o,$newPath);
            }
        }
        else{
            return;
        }
    }
    
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Media
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return Media
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Media
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set caption
     *
     * @param string $caption
     *
     * @return Media
     */
    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }

    /**
     * Get caption
     *
     * @return string
     */
    public function getCaption()
    {
        return $this->caption;
    }

    /**
     * Set alt
     *
     * @param string $alt
     *
     * @return Media
     */
    public function setAlt($alt)
    {
        $this->alt = $alt;

        return $this;
    }

    /**
     * Get alt
     *
     * @return string
     */
    public function getAlt()
    {
        return $this->alt;
    }

    /**
     * Set width
     *
     * @param integer $width
     *
     * @return Media
     */
    public function setWidth($width)
    {
        $this->width = $width;

        return $this;
    }

    /**
     * Get width
     *
     * @return integer
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * Set height
     *
     * @param integer $height
     *
     * @return Media
     */
    public function setHeight($height)
    {
        $this->height = $height;

        return $this;
    }

    /**
     * Get height
     *
     * @return integer
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * Set size
     *
     * @param string $size
     *
     * @return Media
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return string
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Media
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set icon
     *
     * @param string $icon
     *
     * @return Media
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Get icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Media
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set path
     *
     * @param string $path
     *
     * @return Media
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set context
     *
     * @param string $context
     *
     * @return Media
     */
    public function setContext($context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * Get context
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Media
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Media
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Add translation
     *
     * @param \Config\MediaBundle\Entity\Translations\MediaTranslations $translation
     *
     * @return Media
     */
    public function addTranslation(\Config\MediaBundle\Entity\Translations\MediaTranslations $translation)
    {
        $this->translations[] = $translation;
        $translation->setObject($this);
        return $this;
    }

    /**
     * Remove translation
     *
     * @param \Config\MediaBundle\Entity\Translations\MediaTranslations $translation
     */
    public function removeTranslation(\Config\MediaBundle\Entity\Translations\MediaTranslations $translation)
    {
        $this->translations->removeElement($translation);
    }

    /**
     * Get translations
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Add gallery
     *
     * @param \Config\MediaBundle\Entity\Gallery $gallery
     *
     * @return Media
     */
    public function addGallery(\Config\MediaBundle\Entity\Gallery $gallery)
    {
        $this->gallery[] = $gallery;

        return $this;
    }

    /**
     * Remove gallery
     *
     * @param \Config\MediaBundle\Entity\Gallery $gallery
     */
    public function removeGallery(\Config\MediaBundle\Entity\Gallery $gallery)
    {
        $this->gallery->removeElement($gallery);
    }

    /**
     * Get gallery
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * Set realName
     *
     * @param string $realName
     *
     * @return Media
     */
    public function setRealName($realName)
    {
        $this->realName = $realName;

        return $this;
    }

    /**
     * Get realName
     *
     * @return string
     */
    public function getRealName()
    {
        return $this->realName;
    }
}

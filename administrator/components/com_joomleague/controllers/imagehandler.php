<?php
/**
 * @version    $Id: imagehandler.php 4905 2010-01-30 08:51:33Z and_one $
 * @package    JoomlaTracks
 * @copyright  Copyright (C) 2008 Julien Vonthron. All rights reserved.
 * @license    GNU/GPL, see LICENSE.php
 * Joomla Tracks is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access

defined( '_JEXEC' ) or die( 'Restricted access' );

jimport('joomla.application.component.controller');
jimport('joomla.filesystem.file');
require_once (JPATH_COMPONENT.DS.'helpers'.DS.'imageselect.php');

/**
 * Tracks Component Imagehandler Controller
 *
 * @package Joomla
 * @subpackage Tracks
 * @since 0.9
 */
class JoomLeagueControllerImagehandler extends JoomleagueController
{
	/**
	 * Constructor
	 *
	 * @since 0.9
	 */
	function __construct()
	{
		parent::__construct();

		// Register Extra task
	}

	/**
	 * logic for uploading an image
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function upload()
	{
		$mainframe	= JFactory::getApplication();

		// Check for request forgeries
		JRequest::checkToken() or die( 'COM_JOOMLEAGUE_GLOBAL_INVALID_TOKEN' );

		$file	= JRequest::getVar( 'userfile', '', 'files', 'array' );
		//$task	= JRequest::getVar( 'task' );
		$type	= JRequest::getVar( 'type' );
		$folder	= ImageSelectJL::getfolder($type);
		$field	= JRequest::getVar( 'field' );
		$linkaddress	= JRequest::getVar( 'linkaddress' );
		// Set FTP credentials, if given
		jimport( 'joomla.client.helper' );
		JClientHelper::setCredentialsFromRequest( 'ftp' );
		//$ftp = JClientHelper::getCredentials( 'ftp' );

		//set the target directory
		//$base_Dir = JPATH_SITE . DS . 'media' . DS . 'com_joomleague' . DS . $folder . DS;
		$base_Dir = JPATH_SITE . DS . 'images' . DS . 'com_joomleague' . DS .'database'.DS. $folder . DS;
        
        $mainframe->enqueueMessage(JText::_($type),'');
        $mainframe->enqueueMessage(JText::_($folder),'');
        $mainframe->enqueueMessage(JText::_($base_Dir),'');

    //do we have an imagelink?
    if ( !empty( $linkaddress ) )
    {
    $file['name'] = basename($linkaddress);
    
if (preg_match("/dfs_/i", $linkaddress)) 
{
$filename = $file['name'];
}
else
{
    //sanitize the image filename
		$filename = ImageSelectJL::sanitize( $base_Dir, $file['name'] );
}

		
		$filepath = $base_Dir . $filename;
		
if ( !copy($linkaddress,$filepath) )
{
echo "<script> alert('".JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_COPY_FAILED' )."'); window.history.go(-1); </script>\n";
//$mainframe->close();
}
else
{
//echo "<script> alert('" . JText::_( 'COPY COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
echo "<script>  window.parent.selectImage_".$type."('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
//$mainframe->close();
}

    
    }
    
		//do we have an upload?
		if ( empty( $file['name'] ) )
		{
			echo "<script> alert('".JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_CTRL_IMAGE_EMPTY' )."'); window.history.go(-1); </script>\n";
			//$mainframe->close();
		}

		//check the image
		$check = ImageSelectJL::check( $file );

		if ( $check === false )
		{
			$mainframe->redirect( $_SERVER['HTTP_REFERER'] );
		}

		//sanitize the image filename
		$filename = ImageSelectJL::sanitize( $base_Dir, $file['name'] );
		$filepath = $base_Dir . $filename;

		//upload the image
		if ( !JFile::upload( $file['tmp_name'], $filepath ) )
		{
			echo "<script> alert('".JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_FAILED' )."'); window.history.go(-1); </script>\n";
			//$mainframe->close();

		}
		else
		{
//			echo "<script> alert('" . JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE'.'-'.$folder.'-'.$type.'-'.$filename.'-'.$field ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
//			echo "<script> alert('" . JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_CTRL_UPLOAD_COMPLETE' ) . "'); window.history.go(-1); window.parent.selectImage_".$type."('$filename', '$filename','$field'); </script>\n";
      echo "<script>  window.parent.selectImage_".$type."('$filename', '$filename','$field');window.parent.SqueezeBox.close(); </script>\n";
			//$mainframe->close();
		}

	}

	/**
	 * logic to mass delete images
	 *
	 * @access public
	 * @return void
	 * @since 0.9
	 */
	function delete()
	{
		$mainframe	= JFactory::getApplication();
		// Set FTP credentials, if given
		jimport( 'joomla.client.helper' );
		JClientHelper::setCredentialsFromRequest( 'ftp' );

		// Get some data from the request
		$images	= JRequest::getVar( 'rm', array(), '', 'array' );
		$type	= JRequest::getVar( 'type' );

		$folder	= ImageSelectJL::getfolder( $type );

		if ( count( $images ) )
		{
			foreach ( $images as $image )
			{
				if ( $image !== JFilterInput::clean( $image, 'path' ) )
				{
					JError::raiseWarning( 100, JText::_( 'COM_JOOMLEAGUE_ADMIN_IMAGEHANDLER_CTRL_UNABLE_TO_DELETE' ) . ' ' . htmlspecialchars( $image, ENT_COMPAT, 'UTF-8' ) );
					continue;
				}

// 				$fullPath = JPath::clean( JPATH_SITE . DS . 'media' . DS . 'com_joomleague' . DS . $folder . DS . $image );
// 				$fullPaththumb = JPath::clean( JPATH_SITE . DS . 'media' . DS . 'com_joomleague' . DS . $folder . DS . 'small' . DS . $image );
				$fullPath = JPath::clean( JPATH_SITE . DS . 'images' . DS . 'com_joomleague' . DS .'database'.DS. $folder . DS . $image );
				$fullPaththumb = JPath::clean( JPATH_SITE . DS . 'images' . DS . 'com_joomleague' . DS .'database'.DS. $folder . DS . 'small' . DS . $image );
				if ( is_file( $fullPath ) )
				{
					JFile::delete( $fullPath );
					if ( JFile::exists( $fullPaththumb ) )
					{
						JFile::delete( $fullPaththumb );
					}
				}
			}
		}

		$mainframe->redirect( 'index.php?option=com_joomleague&view=imagehandler&type=' . $type . '&tmpl=component' );
	}

}
?>
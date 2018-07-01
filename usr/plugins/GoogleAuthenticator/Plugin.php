<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;
/**
 * Two factor authentication use google authenticator
 * 
 * @package GoogleAuthenticator
 * @author iskyliu
 * @version 1.0.0
 * @link http://typecho.org
 */
class GoogleAuthenticator_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        Typecho_Plugin::factory('admin/login.php')->verificationCode = array('GoogleAuthenticator_Plugin', 'render');
        Typecho_Plugin::factory('admin/login.php')->valid = array('GoogleAuthenticator_Plugin', 'valid');
        Typecho_Plugin::factory('admin/login.php')->secret = array('GoogleAuthenticator_Plugin', 'createSecret');
        Typecho_Plugin::factory('admin/login.php')->qrCodeUrl = array('GoogleAuthenticator_Plugin', 'qrCodeUrl');
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate(){}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        /** 分类名称 */
//        $name = new Typecho_Widget_Helper_Form_Element_Text('word', NULL, 'Hello World', _t('说点什么'));
//        $form->addInput($name);
    }
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form){}
    
    /**
     * 插件实现方法
     * 
     * @access public
     * @return void
     */
    public static function render()
    {
        echo '<p>
                <label for="verificationCode" class="sr-only">'._t('Google Authentication Code').'</label>
                <input type="text" id="verificationCode" name="verificationCode" class="text-l w-100" placeholder="'._t('Google Authentication Code').'" />
            </p>';
    }

    public static function valid($login)
    {
    	include_once 'GoogleAuthenticator.php';
	    $ga = new PHPGangsta_GoogleAuthenticator();
	    if(!$ga->verifyCode($login->user->row['googleCode'], $login->request->verificationCode, 2)){
            $login->widget('Widget_Notice')->set(_t('验证码无效'), 'error
');
            $login->user->logout();
            $login->response->goBack('?referer=' . urlencode($this->reque
st->referer));
        }
    }

    public static function createSecret()
    {
	    include_once 'GoogleAuthenticator.php';
	    $ga = new PHPGangsta_GoogleAuthenticator();
	    try{
		    $secret=$ga->createSecret();
	    } catch (Exception $e){
			return false;
	    };
	    return $secret;
    }

    public static function qrCodeUrl($secret)
    {
	    include_once 'GoogleAuthenticator.php';
	    $ga = new PHPGangsta_GoogleAuthenticator();
	    return $ga->getQRCodeGoogleUrl('Blog', $secret);
    }
    
    public static function validator($validator){
        $validator->addRule('verificationCode', 'required', _t('请输入验证码'));
        $validator->addRule('verificationCode', 'isInteger', _t('验证码必须是数>
字'));
    }
}

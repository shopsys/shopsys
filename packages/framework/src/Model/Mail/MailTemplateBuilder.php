<?php

declare(strict_types=1);

namespace Shopsys\FrameworkBundle\Model\Mail;

use Shopsys\FrameworkBundle\Component\Cdn\CdnFacade;
use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade;

class MailTemplateBuilder
{
    /**
     * @param \Shopsys\FrameworkBundle\Model\Mail\Setting\MailSettingFacade $mailSettingFacade
     * @param \Shopsys\FrameworkBundle\Component\Domain\Domain $domain
     * @param \Shopsys\FrameworkBundle\Component\Cdn\CdnFacade $cdnFacade
     */
    public function __construct(
        protected readonly MailSettingFacade $mailSettingFacade,
        protected readonly Domain $domain,
        protected readonly CdnFacade $cdnFacade,
    ) {
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getContentBaseUrl(int $domainId): string
    {
        return $this->cdnFacade->resolveDomainUrlForAssets($this->domain->getDomainConfigById($domainId));
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterText(int $domainId): string
    {
        return $this->mailSettingFacade->getFooterTextUrl($domainId);
    }

    /**
     * @param int $domainId
     * @return string
     */
    protected function getFooterIcons(int $domainId): string
    {
        $footerIconsHtml = '';
        $itemPadding = '';

        foreach ($this->mailSettingFacade->getFooterIconUrls($domainId) as $footerIconName => $footerIconUrl) {
            if ($footerIconUrl === null) {
                continue;
            }

            $footerIconsHtml .= <<<EOT
                <td {$itemPadding}>
                    <a href="{$footerIconUrl}" style="border:none;text-decoration:none" target="_blank">
                        <img alt="{$footerIconName}" border="0" src="{$this->getContentBaseUrl($domainId)}/public/frontend/mail/{$footerIconName}.png" width="32" height="32" style="color: black; width: 32px; height: 32px;">
                    </a>
                </td>
            EOT;

            $itemPadding = 'style="padding-left:30px;"';
        }

        return $footerIconsHtml;
    }

    /**
     * @param int $domainId
     * @param string|null $content
     * @return string
     */
    public function getMailTemplateWithContent(int $domainId, ?string $content = ''): string
    {
        return <<<EOT
            <body style="box-sizing: border-box; margin: 0;">
                <table width="100%" height="100%" bgcolor="#E5E5E5" class="main-body" style="box-sizing: border-box; min-height: 150px; padding-top: 5px; padding-right: 5px; padding-bottom: 5px; padding-left: 5px; width: 100%; height: 100%; background-color: #E5E5E5;font-family: Arial, Helvetica, sans-serif;">
                    <tbody style="box-sizing: border-box;">
                        <tr valign="top" class="row" style="box-sizing: border-box; vertical-align: top;">
                            <td class="main-body-cell" style="box-sizing: border-box; padding-top: 30px;">
                                <table width="90%" height="0" class="container" style="box-sizing: border-box; font-family: Helvetica, serif; min-height: 150px; margin-top: auto; margin-right: auto; margin-bottom: auto; margin-left: auto; height: 0px; width: 90%; max-width: 700px;">
                                    <tbody style="box-sizing: border-box;">
                                        <tr style="box-sizing: border-box;">
                                            <td bgcolor="#fff" style="box-sizing: border-box; padding-top: 30px; padding-bottom: 30px; padding-left: 30px; padding-right: 30px; background-color: #fff;">
                                                <img src="{$this->getContentBaseUrl($domainId)}/public/frontend/mail/logo.png" style="box-sizing: border-box; color: black; margin: 0;">
                                            </td>
                                        </tr>
                                        <tr style="box-sizing: border-box;">
                                            <td bgcolor="#fff" style="box-sizing: border-box; padding-top: 30px; padding-bottom: 30px; padding-left: 30px; padding-right: 30px; background-color: #fff;" data-gjs-type="editable" class="gjs-editable">
                                                {$content}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                                <table width="100%" height="150" style="box-sizing: border-box; height: 150px; margin: 0 auto 10px auto; padding: 5px 5px 5px 5px; width: 100%;">
                                    <tbody style="box-sizing: border-box;">
                                        <tr style="box-sizing: border-box;">
                                            <td valign="top" align="center" style="box-sizing: border-box; padding: 35px 0 0 0; margin: 0; vertical-align: top;">
                                            <table border="0" cellpadding="0" cellspacing="0" role="presentation" align="center"> 
                                                <tbody> 
                                                    <tr> 
                                                        {$this->getFooterIcons($domainId)}
                                                    </tr> 
                                                </tbody> 
                                            </table>
                                            <table>
                                            </table style="box-sizing: border-box; padding: 10px;">
                                                <tr>
                                                    <td>
                                                    <p style="box-sizing: border-box; font-family: Arial, Helvetica, sans-serif; font-weight: 300; line-height: 19px;text-align: center;" align="center">
                                                        {$this->getFooterText($domainId)}
                                                    </p>
                                                </td>
                                                </tr>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </body>
        EOT;
    }
}

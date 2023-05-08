<?php

class RenderService
{

    public function renderToScreen(Board $board)
    {
        $contentToRender = $this->getContentToRender($board);
        if (empty($contentToRender["error"])) {
            echo $contentToRender["html"];
        } else {
            echo $contentToRender["errot"];
        }
    }

    private function getContentToRender(Board $board)
    {
        $playerCount = $board->getPlayerCount();
        $contentToRender = [
            "html" => "",
            "error" => "",
        ];
        switch ($board->getState()) {
            case 0:
                $contentToRender["html"] = $board->renderIntputsForPlayerCount();
                break;
            case 1:
                $contentToRender["html"] = $board->renderInputsForPlayerNames($playerCount);
                break;
            case 2:
                $contentToRender["html"] = $board->generateView();
                break;
        }
        return $contentToRender;
    }
}

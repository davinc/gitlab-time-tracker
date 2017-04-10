<?php


namespace kriskbx\gtt\Output;


use Illuminate\Support\Collection;
use kriskbx\gtt\Issue;
use kriskbx\gtt\Time;

class MarkdownOutput extends AbstractOutput
{
    /**
     * @var string
     */
    protected $contents = '';

    /**
     * Render.
     *
     * @param Collection $issues
     * @param string $title
     * @param array $params
     *
     * @return void
     */
    public function render(Collection $issues, $title, array $params)
    {
        $params = array_merge($params, [
            'columns'         => $this->columns,
            'delimiter'       => " | ",
            'timesDelimiter'  => "<br><br>",
            'break'           => "<br>",
            'beforeHighlight' => "**",
            'afterHighlight'  => "**",
            'beforeHeadline'  => "***",
            'afterHeadline'   => "***"
        ]);

        $this->contents = '### ' . $title . "\n\n";

        $this->tableHeaders();

        $issues->each(function (Issue $issue) use ($params) {
            $this->addToTotal($issue);
            $this->contents .= "\n| " . $issue->toString($params) . " |";
        });

        $this->total();

        $this->write($this->contents, $this->file);

        $this->cleanUp();
    }

    /**
     * Clean up.
     */
    protected function cleanUp()
    {
        $this->contents = '';
    }

    /**
     * Set total times.
     */
    protected function total()
    {
        $subString = "* **Total:** " . Time::humanReadable($this->totalTime) . "\n";

        collect($this->totalTimeByUser)->each(function ($time, $user) use (&$subString) {
            $subString .= "* **{$user}:** " . Time::humanReadable($time) . "\n";
        });

        $this->contents = $subString . "\n\n" . $this->contents;
    }

    /**
     * Set table headers.
     */
    protected function tableHeaders()
    {
        $this->contents .= "| " . implode(" | ", $this->columns) . " |\n";

        collect($this->columns)->each(function () {
            $this->contents .= "| --- ";
        });

        $this->contents .= "|";
    }
}
def on_page_markdown(markdown, **kwargs):
    return markdown.replace('{{github.link}}', 'https://github.com/shopsys/shopsys/blob/' + str(kwargs['config']['current_version']))

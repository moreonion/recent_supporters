$ = jQuery

class RecentSupportersItem
  constructor: (settings, element) ->
    @settings = settings
    @fn = element.recentSupporters(@settings)

  poll: ->
    callback = (data) =>
      data = data.recent_supporters[@settings.field_name][@settings.delta]
      @fn.update({supporters: data})
      return
    Drupal.behaviors.polling.registry.registerUrl(
      @settings.pollingURL,
      @settings.id,
      callback
    )


class RecentSupportersGlobal
  constructor: (settings, element) ->
    @settings = settings
    @fn = element.recentSupporters(@settings)

  poll: ->
    callback = (data) =>
      data = data.recent_supporters
      @fn.update({supporters: data})
      return
    Drupal.behaviors.polling.registry.registerUrl(
      @settings.pollingURL,
      @settings.id,
      callback,
    )


construct_from_element = ($element) ->
  id = $element.attr('id')
  settings = Drupal.settings.recentSupporters.blocks[id]
  settings['id'] = id

  if settings.allActions
    settings['texts'] = Drupal.settings.recentSupporters.actionTexts
    return new RecentSupportersGlobal(settings, $element)
  else
    return new RecentSupportersItem(settings, $element)


Drupal.behaviors.recent_supporters = {}
Drupal.behaviors.recent_supporters.attach = (context, settings) ->
  $('.recent-supporters-wrapper', context).each(->
    item = construct_from_element($(this))
    if item
      item.poll()
  )

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
    Drupal.behaviors.polling.registry.registerEntity(
      @settings.entity_type,
      @settings.entity_id,
      @settings.id,
      callback
    )

RecentSupportersItem.fromElement = ($element) ->
  id = $element.attr('id')
  settings = Drupal.settings.recentSupporters.blocks[id]
  settings['id'] = id
  if 'field_name' of settings
    return new RecentSupportersItem(settings, $element)
  return false

Drupal.behaviors.recent_supporters = {}
Drupal.behaviors.recent_supporters.attach = (context, settings) ->
  $('.block-recent-supporters', context).each(->
    item = RecentSupportersItem.fromElement($(this))
    if item
      item.poll()
  )

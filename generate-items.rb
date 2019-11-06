require 'faker'

FEED_KIND = "SessionSeries"

def generate_session_series(id)
  {
    "@context": [
      "https://openactive.io/",
      "https://openactive.io/ns-beta"
    ],
    type: FEED_KIND,
    id: "https://openactive.io/session-series##{id}",
    url: "https://openactive.io/session-series##{id}",
    name: Faker::Company.industry,
    # startDate: Faker::Time.between(from: Time.mktime(2019,12,6), to: Time.mktime(2019,12,7)),
    # endDate: Faker::Time.between(from: Time.mktime(2019,12,7), to: Time.mktime(2019,12,8)),
    duration: "PT1H30M",
    location: {
      type: "Place",
      id: "https://openactive.io/place###{Faker::Alphanumeric.alpha(number: 10)}",

    },
    activity: [
      {
        type: "Concept",
        id: "https://openactive.io/activity-list##{Faker::Alphanumeric.alpha(number: 10)}",
        inScheme: "https://openactive.io/activity-list",
        prefLabel: Faker::Esport.game,
      }
    ]

  }
end

items = 5.times.map do |i|
  id = (i+1).to_s
  modified = Time.mktime(2019,11,6).to_i + i*60
  item = {
    id: id,
    modified: modified,
    kind: FEED_KIND,
  }

  # mark 1 in 10 as deleted
  if i % 10 == 2
    item[:status] = "deleted"
  else
    item[:status] = "updated"
    item[:data] = generate_session_series(id)
  end

  item
end

puts items.to_json

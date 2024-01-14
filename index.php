<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

const IBLOCK_EVENTS = 55;
const IBLOCK_PARTICIPANTS = 56;

function fetchEvents(string $date, int $cityId): array
{
    $filter = array(
        'IBLOCK_ID' => IBLOCK_EVENTS,
        'ACTIVE' => 'Y',
        '>=DATE_ACTIVE_FROM' => $date,
        '<=DATE_ACTIVE_TO' => $date,
        'PROPERTY_CITY' => $cityId,
    );

    $select = array(
        'ID',
        'NAME',
        'PROPERTY_PARTICIPANTS',
    );

    $r = \CIBlockElement::GetList(array(), $filter, false, false, $select);

    while ($row = $r->GetNext()) {
        $rows[] = $row;
    }

    return $rows ?? [];
}
function collectEventParticipantId(array $eventList = []): array
{
    $rows  = [];

    foreach ($eventList as $event) {
        $rows = array_merge($event['PROPERTY_PARTICIPANTS_VALUE'], $rows);
    }

    return $rows ?? [];
}
function fetchParticipants(array $participantListId = []): array
{
    if(empty($participantListId)) {
        return [];
    }

    $select = [
        "*"
    ];
    $filter = [
        "ID" => $participantListId,
        "IBLOCK_ID" => IBLOCK_PARTICIPANTS
    ];

    $r = \CIBlockElement::GetList([], $filter, false, false, $select);

    while ($row = $r->GetNext()) {
        $rows[$row['ID']] = $row;
    }

    return $rows ?? [];
}
function findParticipantList(array $participantListId, array $participantList): array
{
    $rows = [];

    foreach ($participantListId as $participantId) {
        $rows[] = $participantList[$participantId];
    }

    return $rows ?? [];
}

// Получаем текущую дату
$currentDate = date('Y-m-d');
// ID конкретного города
$cityId = 23267; // Замените на нужный ID города

// Получаем активные мероприятия на текущую дату в конкретном городе
$eventList = fetchEvents($currentDate, $cityId);

// Собираем массив ид всех участников событий
$participantListId = collectEventParticipantId($eventList);

// Получаем одним запросом всех участников
$participantListAll = fetchParticipants($participantListId);

?>

<?php foreach ($eventList as $event): ?>
    <div style="padding: 10px 0;">
        <div>Events: <?=$event['NAME'];?></div>
        <?php if($participantList = findParticipantList($event['PROPERTY_PARTICIPANTS_VALUE'], $participantListAll)):?>
            <div>Participants:</div>
            <?php foreach ($participantList as $participant):?>
                <div>
                    <?=$participant['NAME'];?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
<?php endforeach; ?>


<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>

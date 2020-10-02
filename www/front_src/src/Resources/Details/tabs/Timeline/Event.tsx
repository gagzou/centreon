import * as React from 'react';

import { makeStyles, Typography } from '@material-ui/core';
import EventIcon from '@material-ui/icons/Event';
import CommentIcon from '@material-ui/icons/Comment';
import NotificationIcon from '@material-ui/icons/Notifications';

import { StatusChip } from '@centreon/ui';
import { prop } from 'ramda';

import { TimelineEvent, Type } from './models';
import {
  labelEvent,
  labelComment,
  labelAcknowledgement,
  labelDowntime,
  labelBy,
  labelFrom,
  labelTo,
  labelNotificationSentTo,
  labelTries,
  labelNotification,
} from '../../../translatedLabels';
import { getFormattedTime, getFormattedDateTime } from '../../../dateTime';
import DowntimeChip from '../../../Chip/Downtime';
import AcknowledgeChip from '../../../Chip/Acknowledge';
import truncate from '../../../truncate';

const types: Array<Type> = [
  {
    id: 'event',
    name: labelEvent,
  },

  {
    id: 'notification',
    name: labelNotification,
  },

  {
    id: 'comment',
    name: labelComment,
  },

  {
    id: 'acknowledgement',
    name: labelAcknowledgement,
  },

  {
    id: 'downtime',
    name: labelDowntime,
  },
];

const getTypeIds = (): Array<string> => {
  return types.map(prop('id'));
};

const useStyles = makeStyles((theme) => ({
  info: {
    display: 'grid',
    gridAutoFlow: 'row',
    gridGap: theme.spacing(0.5),
  },
  title: {
    display: 'grid',
    gridAutoFlow: 'column',
    gridAutoColumns: 'auto',
    gridGap: theme.spacing(2),
    justifyContent: 'flex-start',
    alignItems: 'center',
  },
}));

interface Props {
  event: TimelineEvent;
}

const Date = ({ event }: Props): JSX.Element => (
  <Typography variant="caption">{getFormattedTime(event.date)}</Typography>
);

const Content = ({ event }: Props): JSX.Element => {
  const { content } = event;

  return <Typography variant="caption">{truncate(content)}</Typography>;
};

const Author = ({ event, label }: Props & { label: string }): JSX.Element => {
  const suffix = event.contact ? `${labelBy} ${event.contact.name}` : '';

  return <Typography variant="h6">{`${label} ${suffix}`}</Typography>;
};

const EventTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <EventIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{labelEvent}</Typography>
          <StatusChip
            severityCode={event.status?.severityCode as number}
            label={event.status?.name}
          />
        </div>
        <Content event={event} />
      </div>
      <Typography>{`${labelTries}: ${event.tries}`}</Typography>
    </>
  );
};

const CommentTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <CommentIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} label={labelComment} />
        </div>
        <Content event={event} />
      </div>
    </>
  );
};

const AcknowledgeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <AcknowledgeChip />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} label={labelAcknowledgement} />
        </div>
        <Content event={event} />
      </div>
    </>
  );
};

const DowntimeTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <DowntimeChip />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Author event={event} label={labelDowntime} />
        </div>
        <Typography variant="caption">
          {`${labelFrom} ${getFormattedDateTime(
            event.startDate,
          )} ${labelTo} ${getFormattedDateTime(event.endDate)}`}
        </Typography>
        <Content event={event} />
      </div>
    </>
  );
};

const NotificationTimelineEvent = ({ event }: Props): JSX.Element => {
  const classes = useStyles();

  return (
    <>
      <NotificationIcon color="primary" />
      <div className={classes.info}>
        <Date event={event} />
        <div className={classes.title}>
          <Typography variant="h6">{`${labelNotificationSentTo} ${event.contact?.name}`}</Typography>
        </div>
        <Content event={event} />
      </div>
    </>
  );
};

const TimelineEventByType = {
  event: EventTimelineEvent,
  notification: NotificationTimelineEvent,
  comment: CommentTimelineEvent,
  acknowledgement: AcknowledgeTimelineEvent,
  downtime: DowntimeTimelineEvent,
};

export { TimelineEventByType, types, getTypeIds };
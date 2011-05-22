CREATE TABLE queue (
    id serial NOT NULL PRIMARY KEY,
    email character varying(255) NOT NULL,
    filename text NOT NULL,
    timesubmitted integer NOT NULL,
    timestarted integer,
    timeconverted integer,
    log text
);

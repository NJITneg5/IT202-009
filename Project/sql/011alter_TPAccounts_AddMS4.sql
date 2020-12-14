ALTER TABLE `TPAccounts`
    ADD COLUMN apy              decimal(7,5) default null,
    ADD COLUMN nextApy          date default null,
    ADD COLUMN active           varchar(5) default 'true',
    ADD COLUMN frozen           varchar(5) default 'false'
/* XMB Application Variables - These are needed for backward and forward theme compatibility */

.admin-members-result {
    --xmb-grid-columns: max-content auto auto max-content auto auto auto;
}
.admin-attachment-result {
    --xmb-grid-columns: auto 20% 28% 11% max-content max-content;
}
@media screen and (max-width: 850px) {
    .admin-attachment-result {
        --xmb-grid-columns: auto 20% 28% max-content;
    }
}
.admin-log {
    --xmb-grid-columns: auto auto auto auto auto;
}
.admin-ranks {
    --xmb-grid-columns: max-content auto auto auto auto auto;
}
.admin-modlog {
    --xmb-grid-columns: auto auto auto auto;
}
.admin-restrictions {
    --xmb-grid-columns: max-content auto max-content max-content;
}
.admin-smilies {
    --xmb-grid-columns: max-content auto auto max-content;
}
.admin-censor {
    --xmb-grid-columns: auto auto auto;
}
.admin-ipban {
    --xmb-grid-columns: min-content auto auto;
}
.admin-themes {
    --xmb-grid-columns: max-content auto max-content;
}
.admin-themes-single {
    --xmb-grid-columns: auto auto max-content;
}
.admin-attachment-search {
    --xmb-grid-columns: max-content auto;
}
.breadcrumbs {
    --xmb-grid-columns: auto min-content min-content;
}
.admin-email,
.admin-forums-detail,
.admin-moderators,
.admin-newsletter,
.admin-prune,
.admin-settings,
.admin-themes-import,
.admin-themes-new,
.header-top-grid,
.links-grid {
    --xmb-grid-columns: auto auto;
}
.admin-restrictions-new {
    --xmb-grid-columns: max-content max-content;
}
.admin-search-result {
    --xmb-grid-columns: 5% auto;
}
.admin-members-search,
.admin-rename {
    --xmb-grid-columns: 22% auto;
}

@media screen and (max-width: 850px) {
    .admin-attachment-result .cell:nth-child(4),
    .admin-attachment-result .cell:nth-child(6) {
        --xmb-column-hide: none;
    }
}
.span,
.admin-themes-single .row .field.submit,
.header-top-grid .logo {
    --xmb-grid-span: 1 / -1;
}
.admin-smilies .add-all .span,
.admin-themes-single .field.span {
    --xmb-grid-span: 2 / -1;
}
.admin-smilies .icon-list .span {
    --xmb-grid-span: 2 / span 2;
}
.admin-smilies .new .span {
    --xmb-grid-span: 3 / -1;
}

.header-top-grid {
    --xmb-grid-rows: auto auto;
}

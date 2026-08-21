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
.admin-ranks {
    --xmb-grid-columns: max-content auto auto auto auto auto;
}
.admin-restrictions {
    --xmb-grid-columns: max-content auto max-content max-content;
}
.admin-attachment-search {
    --xmb-grid-columns: max-content auto;
}
.admin-censor {
    --xmb-grid-columns: auto auto auto;
}
.admin-ipban {
    --xmb-grid-columns: min-content auto auto;
}
.admin-email,
.admin-forums-detail,
.admin-moderators,
.admin-newsletter,
.admin-prune,
.admin-settings {
    --xmb-grid-columns: auto auto;
}
.admin-restrictions-new {
    --xmb-grid-columns: max-content max-content;
}
.admin-search-result {
    --xmb-grid-columns: 5% auto;
}
.admin-members-search {
    --xmb-grid-columns: 22% auto;
}

.admin-members-result .span {
    --xmb-grid-span: 1 / span 7;
}
.admin-attachment-result .span,
.admin-ranks .span {
    --xmb-grid-span: 1 / span 6;
}
@media screen and (max-width: 850px) {
    .admin-attachment-result .cell:nth-child(4),
    .admin-attachment-result .cell:nth-child(6) {
        --xmb-column-hide: none;
    }
    .admin-attachment-result .span {
        --xmb-grid-span: 1 / span 4;
    }
}
.admin-restrictions .span {
    --xmb-grid-span: 1 / span 4;
}
.admin-censor .span,
.admin-ipban .span {
    --xmb-grid-span: 1 / span 3;
}
.admin-attachment-search .span,
.admin-email .span,
.admin-forums-detail .span,
.admin-members-search .span,
.admin-moderators .span,
.admin-newsletter .span,
.admin-prune .span,
.admin-ranks .span.new-name,
.admin-search-result .span,
.admin-settings .span {
    --xmb-grid-span: 1 / span 2;
}

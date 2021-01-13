<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Ampache\Module\Artist\Deletion;

use Ampache\Model\Art;
use Ampache\Model\Artist;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Model\Rating;
use Ampache\Model\Shoutbox;
use Ampache\Model\Useractivity;
use Ampache\Model\Userflag;
use Ampache\Module\Album\Deletion\AlbumDeleterInterface;
use Ampache\Module\Album\Deletion\Exception\AlbumDeletionException;
use Ampache\Module\Artist\Deletion\Exception\ArtistDeletionException;
use Ampache\Module\System\LegacyLogger;
use Ampache\Repository\AlbumRepositoryInterface;
use Ampache\Repository\ArtistRepositoryInterface;
use Ampache\Repository\ShoutRepositoryInterface;
use Psr\Log\LoggerInterface;

final class ArtistDeleter implements ArtistDeleterInterface
{
    private AlbumDeleterInterface $albumDeleter;

    private ArtistRepositoryInterface $artistRepository;

    private AlbumRepositoryInterface $albumRepository;

    private ModelFactoryInterface $modelFactory;

    private LoggerInterface $logger;

    private ShoutRepositoryInterface $shoutRepository;

    public function __construct(
        AlbumDeleterInterface $albumDeleter,
        ArtistRepositoryInterface $artistRepository,
        AlbumRepositoryInterface $albumRepository,
        ModelFactoryInterface $modelFactory,
        LoggerInterface $logger,
        ShoutRepositoryInterface $shoutRepository
    ) {
        $this->albumDeleter     = $albumDeleter;
        $this->artistRepository = $artistRepository;
        $this->albumRepository  = $albumRepository;
        $this->modelFactory     = $modelFactory;
        $this->logger           = $logger;
        $this->shoutRepository  = $shoutRepository;
    }

    /**
     * @throws ArtistDeletionException
     */
    public function remove(
        Artist $artist
    ): void {
        $album_ids = $this->albumRepository->getByArtist($artist);

        foreach ($album_ids as $albumId) {
            $album = $this->modelFactory->createAlbum($albumId);

            try {
                $this->albumDeleter->delete($album);
            } catch (AlbumDeletionException $e) {
                $this->logger->critical(
                    sprintf(
                        "Error when deleting the album `%d`.",
                        $albumId
                    ),
                    [LegacyLogger::CONTEXT_TYPE => __CLASS__]
                );

                throw new ArtistDeletionException();
            }
        }

        $deleted = $this->artistRepository->delete($artist->id);
        if ($deleted) {
            Art::garbage_collection('artist', $artist->id);
            Userflag::garbage_collection('artist', $artist->id);
            Rating::garbage_collection('artist', $artist->id);
            $this->shoutRepository->collectGarbage('artist', $artist->id);
            Useractivity::garbage_collection('artist', $artist->id);
        }
    }
}
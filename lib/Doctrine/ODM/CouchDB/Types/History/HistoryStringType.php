<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\ODM\CouchDb\Types\History;

use Doctrine\ODM\CouchDb\Types\DateTimeType;
use Doctrine\ODM\CouchDb\Types\StringType;

/**
 * The history String type.
 *
 * @author Thomas Rothe <th.rothe@gmail.com>
 */
class HistoryStringType extends StringType
{
    public function convertToDatabaseValue($values)
    {
        foreach ($values as &$value) {
            $value['validFrom'] = DateTimeType::convertPHPToDatabaseValue($value['validFrom']);
            $value['value'] = parent::convertToDatabaseValue($value['value']);
            if (isset($value['validUntil'])) {
                $value['validUntil'] = DateTimeType::convertPHPToDatabaseValue($value['validUntil']);
            }
        }
        return (array) $values;
    }
}
